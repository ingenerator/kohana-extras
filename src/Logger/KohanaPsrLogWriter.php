<?php


namespace Ingenerator\KohanaExtras\Logger;


use Ingenerator\PHPUtils\Logging\ExternalCallSiteFinder;
use Ingenerator\PHPUtils\Logging\StackdriverApplicationLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class KohanaPsrLogWriter extends \Log_Writer
{
    /**
     * @var ExternalCallSiteFinder
     */
    protected $call_site_finder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $level_map = [
        \Kohana_Log::EMERGENCY => LogLevel::EMERGENCY,
        \Kohana_Log::ALERT     => LogLevel::ALERT,
        \Kohana_Log::CRITICAL  => LogLevel::CRITICAL,
        \Kohana_Log::ERROR     => LogLevel::ERROR,
        \Kohana_Log::WARNING   => LogLevel::WARNING,
        \Kohana_Log::NOTICE    => LogLevel::NOTICE,
        \Kohana_Log::INFO      => LogLevel::INFO,
        \Kohana_Log::DEBUG     => LogLevel::DEBUG,
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger           = $logger;
        $this->call_site_finder = new ExternalCallSiteFinder;
    }

    public function write(array $messages)
    {
        $source_location = $this->call_site_finder->findExternalCall(
            \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            [static::class, \Log::class]
        );

        foreach ($messages as $message) {
            // Add the trace to the message if one hasn't already been set
            if ( ! isset($message['additional'][StackdriverApplicationLogger::PROP_SOURCE_LOCATION])) {
                $message['additional'][StackdriverApplicationLogger::PROP_SOURCE_LOCATION] = $source_location;
            }
            $this->writeMessage($message);
        }

    }

    protected function convertKohanaToPsrLogLevel($kohana_level): string
    {
        if ( ! isset($this->level_map[$kohana_level])) {
            throw new \InvalidArgumentException('Unknown kohana log level '.$kohana_level);
        }

        return $this->level_map[$kohana_level];
    }

    protected function writeMessage(array $message): void
    {
        $time = (new \DateTimeImmutable)->setTimestamp($message['time']);

        $this->logger->log(
            $this->convertKohanaToPsrLogLevel($message['level']),
            $message['body'],
            $message['additional'] + ['time' => $time->format(\DateTimeInterface::RFC3339_EXTENDED)]
        );
    }

}
