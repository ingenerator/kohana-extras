<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\ExceptionHandling;

use Kohana_Exception;

/**
 * Base for an exception handler that supports enforcing type-safety of the argument
 * without strict hints (which are different for PHP5.x vs PHP7.x) and for logging if
 * a log has been initialised
 *
 * @package Ingenerator\KohanaExtras\ExceptionHandling
 */
abstract class AbstractExceptionHandler implements ExceptionHandler
{


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
     * @param \Exception|\Throwable $e
     *
     * @return \Response|null
     */
    public function handle($e)
    {
        if (($e instanceof \Exception) OR ($e instanceof \Throwable)) {
            return $this->doHandle($e);
        }

        $type = \is_object($e) ? \get_class($e) : \gettype($e);
        throw new \InvalidArgumentException('Expected Exception|Throwable, got '.$type);
    }

    /**
     * @param \Exception|\Throwable $e
     *
     * @return \Response|null
     */
    abstract protected function doHandle($e);

    /**
     * @param \Exception|\Throwable $e
     */
    protected function logException($e)
    {
        if ($this->log) {
            $log = $this->log;
        } elseif (\class_exists(\Kohana::class, FALSE) AND \Kohana::$log) {
            $log = \Kohana::$log;
        } else {
            throw new \RuntimeException('No logger to log: ['.\get_class($e).'] '.$e->getMessage(), 0, $e);
        }

        $log->add(\Log::EMERGENCY, Kohana_Exception::text($e), NULL, ['exception' => $e]);
        $log->write();
    }
}
