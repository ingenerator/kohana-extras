<?php


namespace Ingenerator\KohanaExtras\GarbageCollector;


use Ingenerator\PHPUtils\Session\MysqlSession;
use Psr\Log\LoggerInterface;

class MysqlSessionGarbageCollector implements GarbageCollector
{
    private MysqlSession    $session_handler;
    private LoggerInterface $logger;

    public function __construct(
        MysqlSession $session_handler,
        LoggerInterface $logger
    ) {
        $this->session_handler = $session_handler;
        $this->logger          = $logger;
    }

    public function execute(): void
    {
        $collected = $this->session_handler->garbageCollect();
        $this->logger->info('Garbage collected '.$collected.' sessions');
    }

}
