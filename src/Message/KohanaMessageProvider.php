<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\Message;

use Psr\Log\LoggerInterface;

/**
 * Dependency inversion wrapper around \Kohana::message() that also supports replacing parameters
 * into messages and logging if a message does not exist.
 *
 * @package Ingenerator\KohanaWrapper
 */
class KohanaMessageProvider
{
    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
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
            return \strtr($message, $params);
        }

        $message_id = $file.':'.$path;

        $this->log->warning(
            "Undefined message '$message_id'",
            ['exception' => new \InvalidArgumentException('Undefined message `'.$message_id.'`')]
        );

        return $message_id;
    }

}
