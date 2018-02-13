<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Message;

/**
 * Dependency inversion wrapper around \Kohana::message() that also supports replacing parameters
 * into messages and logging if a message does not exist.
 *
 * @package Ingenerator\KohanaWrapper
 */
class KohanaMessageProvider
{
    /**
     * @var \Log
     */
    protected $log;

    /**
     * @param \Log $log
     */
    public function __construct(\Log $log)
    {
        $this->log = $log;
    }

    /**
     * Get a message with the given message file and path, optionally replacing any parameters in
     * the message. If the message is not defined then:
     *
     *  * if a default is provided, it will be returned
     *  * if no default is provided, the message file and path will be returned and a warning
     *    will be logged.
     *
     * @param string   $file    The message file to look in
     * @param string   $path    dot-separated path to the message in the message file
     * @param string[] $params  key=>value hash of variables to replace
     * @param string   $default default message to return if undefined
     *
     * @return string
     */
    public function message($file, $path, array $params = [], $default = NULL)
    {
        if ($message = \Kohana::message($file, $path, $default)) {
            return strtr($message, $params);
        }

        $message_id = $file.':'.$path;

        $this->log->add(
            \Log::WARNING,
            "Undefined message '$message_id' requested by ".$this->formatCallerTrace(
                debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)
            )
        );

        return $message_id;
    }

    /**
     * @param array $trace
     *
     * @return string
     */
    protected function formatCallerTrace(array $trace)
    {
        $trace  = array_pad($trace, 2, []);
        $values = \Arr::flatten(
            \Arr::extract(
                $trace,
                ['1.class', '1.function', '0.file', '0.line'],
                '?'
            )
        );

        return strtr('class::function @ file[line]', $values);
    }
}
