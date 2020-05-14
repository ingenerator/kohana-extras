<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\PHPUtils\Logging\StackdriverApplicationLogger;
use Psr\Log\LoggerInterface;

class RequestExceptionDispatcher
{

    /**
     * @var array[] Override this to send emergency fallback logs elsewhere
     *
     * @see FallbackLogger::makeBestLogger()
     */
    public static $fallbackLoggerFactories = [
        // Attempt to log to the StackdriverApplicationLogger if it exists
        // If not it will fall back to the built-in FallbackLogger to stderr by default
        [StackdriverApplicationLogger::class, 'instance'],
    ];

    /**
     * @var ExceptionHandler
     */
    protected $default_handler;

    /**
     * @var DependencyContainer
     */
    protected $dependencies;

    /**
     * @var array
     */
    protected $handler_map = [];

    /**
     * @var bool
     */
    protected $is_fallback = FALSE;

    /**
     * @var LoggerInterface|FallbackLogger
     */
    protected $logger;

    /**
     * Handle the exception and render the response, using the full handler stack if possible
     * or the fallback if not.
     *
     * @param \Exception|\Throwable $exception
     * @param string                $container_class
     * @param string                $container_method
     * @param string                $service_key
     */
    public static function dispatch(
        $exception,
        $container_class = 'Dependencies',
        $container_method = 'instance',
        $service_key = 'exception_handler.dispatcher'
    ) {
        static::send(
            static::factory($container_class, $container_method, $service_key)
                ->handle($exception)
        );
    }

    /**
     * Default method of creating an instance, gets it from the container if safe or a fallback if not
     *
     * @param string $container_class
     * @param string $container_method
     * @param string $service_key
     *
     * @return RequestExceptionDispatcher
     */
    public static function factory($container_class, $container_method, $service_key)
    {
        try {
            if ( ! \class_exists($container_class, FALSE)) {
                throw new \InvalidArgumentException('No container class '.$container_class);
            }
            if ( ! \method_exists($container_class, $container_method)) {
                throw new \InvalidArgumentException('No method '.$container_class.'::'.$container_method);
            }
            $container = \call_user_func([$container_class, $container_method]);

            return $container->get($service_key);
        } catch (\Exception $e) {
            $fallback = static::factoryFallback();
            $fallback->logException($e);

            return $fallback;
        }
    }

    /**
     * Gets an instance that just logs to the best logger it can find and returns the generic error response
     *
     * The logger in use can be customised by overriding the ::$fallbackLoggerFactories property
     *
     * @return static
     */
    public static function factoryFallback()
    {
        require_once __DIR__.'/FallbackLogger.php';
        $inst              = new static(
            NULL,
            NULL,
            FallbackLogger::makeBestLogger(static::$fallbackLoggerFactories),
            []
        );
        $inst->is_fallback = TRUE;

        return $inst;
    }

    /**
     * Send a response produced by the handler
     *
     * @param \Response|array $response
     */
    public static function send($response)
    {
        // Discard all previous output but ONLY if we're not running in phpunit
        // This is because phpunit needs to use output buffering to capture output for testing
        if ( ! \class_exists(\PHPUnit\Framework\TestCase::class, FALSE)) {
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        if ($response instanceof \Response) {
            $response->send_headers(TRUE);
            echo $response->body();
        } elseif (\is_array($response)) {
            if ( ! \headers_sent()) {
                \http_response_code($response['code']);
                foreach ($response['headers'] as $header => $value) {
                    \header($header.': '.$value, TRUE);
                }
            }
            echo $response['body'];
        } else {
            // This is really bad. WTF did we get?
            \http_response_code(500);
            echo static::class.' - Unexpected response type';
        }
    }

    /**
     * @param ExceptionHandler|NULL          $default_handler
     * @param DependencyContainer|NULL       $dependencies
     * @param LoggerInterface|FallbackLogger $logger In reality, may be any object with an ->emergency method
     * @param array                          $handler_map
     */
    public function __construct(
        ?ExceptionHandler $default_handler,
        ?DependencyContainer $dependencies,
        object $logger,
        array $handler_map = []
    ) {
        $this->default_handler = $default_handler;
        $this->dependencies    = $dependencies;
        $this->logger          = $logger;
        $this->handler_map     = $handler_map;
    }

    /**
     * Attempts to handle the provided exception and generate a response
     *
     * @param \Throwable $exception
     *
     * @return array|null|\Response
     */
    public function handle(\Throwable $exception)
    {
        if ($this->is_fallback) {
            return $this->handleFallback($exception);
        }

        try {
            foreach ($this->handler_map as $handler_def) {
                if ($exception instanceof $handler_def['type']) {
                    if ( ! $this->dependencies) {
                        throw new \RuntimeException('No container to get handlers');
                    }
                    $handler = $this->dependencies->get($handler_def['handler']);
                    if ($response = $handler->handle($exception)) {
                        return $response;
                    }
                }
            }

            if ( ! $this->default_handler) {
                throw new \RuntimeException('No default handler');
            }

            return $this->default_handler->handle($exception);
        } catch (\Exception $handling_exception) {
            $this->logException($handling_exception);

            return $this->handleFallback($exception);
        }
    }

    /**
     * @param \Throwable $exception
     *
     * @return array
     */
    protected function handleFallback(\Throwable $exception)
    {
        $this->logException($exception);

        return $this->getFallbackErrorResponse();
    }

    /**
     * Log this exception into syslog (or your overridden callable)
     *
     * @param \Throwable $exception
     */
    protected function logException(\Throwable $exception)
    {
        $this->logger->emergency(
            \sprintf(
                '[%s] %s (%s:%s)',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ),
            ['exception' => $exception]
        );
    }

    /**
     * @return array
     */
    protected function getFallbackErrorResponse()
    {

        return [
            'code'    => 500,
            'headers' => [
                'Content-Type' => 'text/html;charset=utf8',
            ],
            'body'    => \file_get_contents(__DIR__.'/../../resources/generic_error_page.html'),
        ];
    }
}
