<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\ExceptionHandling;

use Kohana_Exception;

/**
 * Base for an exception handler that supports logging if a log has been initialised
 *
 * @package Ingenerator\KohanaExtras\ExceptionHandling
 */
abstract class AbstractExceptionHandler implements ExceptionHandler
{

    const PAGE_GENERIC_ERROR = 'generic_error_page.html';
    const PAGE_GENERIC_MAINTENANCE = 'generic_maintenance_page.html';


    /**
     * @var \Log
     */
    protected $log;

    /**
     * @param \Log|NULL $log will default to \Kohana::$log if nothing is injected
     */
    public function __construct(\Log $log = NULL)
    {
        $this->log = $log;
    }

    /**
     * @param \Throwable $e
     *
     * @return \Response|null
     */
    public function handle(\Throwable $e)
    {
        // Historic stub retained for BC, it was just there to allow us to enforce that we had
        // either a Throwable or an Exception.
        //
        // In a future breaking release
        return $this->doHandle($e);
    }

    /**
     * @param \Throwable $e
     *
     * @return \Response|null
     */
    abstract protected function doHandle(\Throwable $e);

    /**
     * @param \Throwable $e
     */
    protected function logException(\Throwable $e)
    {
        if ($this->log) {
            $log = $this->log;
        } elseif (\class_exists(\Kohana::class, FALSE) AND \Kohana::$log) {
            $log = \Kohana::$log;
        } else {
            throw new \RuntimeException(
                'No logger to log: ['.\get_class($e).'] '.$e->getMessage(),
                0,
                $e
            );
        }

        $log->add(\Log::EMERGENCY, Kohana_Exception::text($e), NULL, ['exception' => $e]);
        $log->write();
    }

    protected function respondGenericErrorPage(string $resource_file, int $status_code): \Response
    {
        $response = new \Response;
        $response->status($status_code);
        $response->headers('Content-Type', 'text/html;charset=utf8');
        $response->body(\file_get_contents(__DIR__.'/../../resources/'.$resource_file));

        return $response;
    }
}
