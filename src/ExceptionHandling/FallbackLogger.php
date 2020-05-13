<?php


namespace Ingenerator\KohanaExtras\ExceptionHandling;


use Psr\Log\LoggerInterface;
use Throwable;

/**
 * A bare-bones logger designed for emergency use when no other logger can be found
 *
 * This logger is used if the RequestExceptionDispatcher cannot identify any other logger to use. There is a strong
 * chance this means that the whole system is not properly bootstrapped, and perhaps that autoloading logic is broken
 * or files missing. We do not therefore officially declare compatibility with the PSR LoggerInterface to keep any
 * dependencies or runtime logic to the absolute bare minimum.
 *
 * Applications can provide their own FallbackLoggers - the RequestExceptionDispatcher uses duck-typing rather than
 * hard typing and just expects a class with an `->emergency($message, $context)` method.
 *
 * See `::makeBestLogger` for information on how we select a fallback logger if required.
 */
class FallbackLogger
{
    /**
     * @var string
     */
    protected $output_stream;

    /**
     * Finds first available logger from a preference-ranked list of static providers, or falls back to this class
     *
     * This is called when we cannot get a RequestExceptionDispatcher from the dependency container for some reason. It
     * tries its best to get a logger we actually want to use, before giving up and reverting to providing an instance
     * of this class (which logs a basic JSON message to php://stderr).
     *
     * To customise the loggers we attempt to use, set RequestExceptionDispatcher::$fallbackLoggerFactories to a list of
     * class/static method pairs eg:
     *
     *      RequestExceptionDispatcher::$fallbackLoggerFactories = [
     *        [CustomClass::class, 'instance'],
     *        [LessGoodLogger::class, 'newLogger'],
     *        [BasicLogger::class, 'ohMan'],
     *      ];
     *
     * Note that because the system is already highly broken by this point we play it very safe and do not attempt to
     * log or resolve any errors encountered during logger construction.
     *
     *   * This is not a general `callable` array - you must provide pairs of (string) class name and (string) static
     *     method name.
     *   * If you reference a class that has not yet been loaded, it will be ignored without any attempt to
     *     autoload.
     *   * If you reference a static method that does not exist, is not static, or throws any exception, it will
     *     be silently ignored.
     *   * Returned logger instances can be of any type but must have a `public function emergency()` or they will
     *     be skipped.
     *
     * @param array $factories Of [$class, $factory_method] pairs
     *
     * @return FallbackLogger|LoggerInterface
     */
    public static function makeBestLogger($factories)
    {
        if ( ! \is_array($factories)) {
            // Ignore invalid input
            $factories = [];
        }

        foreach ($factories as $factory) {
            if ( ! \is_array($factory)) {
                // Skip it, not valid
                continue;
            }

            if (\count($factory) !== 2) {
                // Skip it, need two elements for callable
                continue;
            }

            if ( ! \class_exists($factory[0], FALSE)) {
                // Skip it, class hasn't already been loaded
                continue;
            }

            try {
                $logger = \call_user_func($factory);
                if (\method_exists($logger, 'emergency')) {
                    return $logger;
                }
            } catch (\Throwable $e) {
                // Do nothing - this logger definition is not currently usable
            }
        }

        return new static;
    }

    public function __construct(string $output_stream = 'php://stderr')
    {
        $this->output_stream = $output_stream;
    }

    public function emergency($message, array $context = [])
    {
        // This internally duplicates the most basic parts of StackdriverApplicationLogger - this
        // is intentional: if we have got here then really everything is broken...
        $entry = [
            'severity' => 'EMERGENCY',
            'message'  => $message,
        ];
        if (isset($context['exception'])) {
            $entry['exception'] = $this->formatException($context['exception']);
        }

        \file_put_contents($this->output_stream, \json_encode($entry)."\n", FILE_APPEND);
    }

    protected function formatException($e)
    {
        if ( ! $e instanceof Throwable) {
            return $e;
        }

        $result = [
            'class' => get_class($e),
            'msg'   => $e->getMessage(),
            'code'  => $e->getCode(),
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
        ];

        if ($previous = $e->getPrevious()) {
            $result['previous'] = $this->formatException($previous);
        }

        return $result;
    }

}
