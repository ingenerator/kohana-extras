<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;

class RequestExceptionDispatcher
{

    /**
     * @var callable Override this if you want to send emergency fallback logs somewhere else
     */
    public static $syslog_func = 'syslog';

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
            if ( ! class_exists($container_class, FALSE)) {
                throw new \InvalidArgumentException('No container class '.$container_class);
            }
            if ( ! method_exists($container_class, $container_method)) {
                throw new \InvalidArgumentException('No method '.$container_class.'::'.$container_method);
            }
            $container = call_user_func([$container_class, $container_method]);

            return $container->get($service_key);
        } catch (\Exception $e) {
            $fallback = static::factoryFallback();
            $fallback->logException($e);

            return $fallback;
        }
    }

    /**
     * Gets an instance that just logs to syslog and returns the generic error response
     *
     * @return static
     */
    public static function factoryFallback()
    {
        $inst              = new static;
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
        if ( ! class_exists(\PHPUnit_Framework_TestCase::class, FALSE)) {
            while (ob_get_level()) {
                ob_end_clean();
            }
        }

        if ($response instanceof \Response) {
            $response->send_headers(TRUE);
            echo $response->body();
        } elseif (is_array($response)) {
            if ( ! headers_sent()) {
                http_response_code($response['code']);
                foreach ($response['headers'] as $header => $value) {
                    header($header.': '.$value, TRUE);
                }
            }
            echo $response['body'];
        } else {
            // This is really bad. WTF did we get?
            http_response_code(500);
            echo static::class.' - Unexpected response type';
        }
    }

    /**
     * @param ExceptionHandler|NULL    $default_handler
     * @param DependencyContainer|NULL $dependencies
     * @param array                    $handler_map
     */
    public function __construct(
        ExceptionHandler $default_handler = NULL,
        DependencyContainer $dependencies = NULL,
        array $handler_map = []
    ) {
        $this->default_handler = $default_handler;
        $this->dependencies    = $dependencies;
        $this->handler_map     = $handler_map;
    }

    /**
     * Attempts to handle the provided exception and generate a response
     *
     * @param \Exception|\Throwable $exception
     *
     * @return array|null|\Response
     */
    public function handle($exception)
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
     * @param \Exception|\Throwable $exception
     *
     * @return array
     */
    protected function handleFallback($exception)
    {
        $this->logException($exception);

        return $this->getFallbackErrorResponse();
    }

    /**
     * Log this exception into syslog (or your overridden callable)
     *
     * @param \Exception|\Throwable $exception
     */
    protected function logException($exception)
    {
        $chain_index = 0;
        do {
            $msg = sprintf(
                '%s[%s] %s (%s:%s)',
                $chain_index > 0 ? '(cause #'.$chain_index.') ' : '',
                get_class($exception),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
            call_user_func(static::$syslog_func, LOG_EMERG, $msg);
            $chain_index++;
        } while ($exception = $exception->getPrevious());
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
            'body'    => file_get_contents(__DIR__.'/../../resources/generic_error_page.html'),
        ];
    }
}
