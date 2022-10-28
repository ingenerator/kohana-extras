<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DebugBar;


use Psr\Log\LogLevel;

/**
 * Log handler that writes messages to the debug bar
 *
 * @package Ingenerator\Debug
 */
class DebugLogWriter extends \Log_Writer
{
    private static $level_map = [
        LOG_DEBUG   => LogLevel::DEBUG,
        LOG_INFO    => LogLevel::INFO,
        LOG_WARNING => LogLevel::WARNING,
        LOG_ERR     => LogLevel::ERROR,
        LOG_CRIT    => LogLevel::CRITICAL,
        LOG_ALERT   => LogLevel::ALERT
    ];

    /**
     * @var \DebugBar\DataCollector\MessagesCollector
     */
    protected $messages;

    /**
     * @var \DebugBar\DataCollector\ExceptionsCollector
     */
    protected $exceptions;


    public function __construct(\DebugBar\DebugBar $bar)
    {
        $this->messages   = $bar['messages'];
        $this->exceptions = $bar['exceptions'];
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $messages)
    {
        foreach ($messages as $message) {
            if (isset($message['additional']['exception'])) {
                $this->exceptions->addThrowable($message['additional']['exception']);
            }

            $file  = \preg_replace('#^'.\preg_quote(BASEDIR, '#').'#', '', (string) $message['file']);
            $level = \Arr::get(static::$level_map, $message['level'], LogLevel::ERROR);

            $this->messages->log(
                $level,
                \sprintf(
                    '%s [%s:%s]',
                    $message['body'],
                    $file,
                    $message['line']
                )
            );
        }
    }

}
