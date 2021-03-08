<?php


namespace test\unit\KohanaExtras\GarbageCollector;


use Ingenerator\KohanaExtras\GarbageCollector\MysqlSessionGarbageCollector;
use Ingenerator\PHPUtils\Session\MysqlSession;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class MysqlSessionGarbageCollectorTest extends TestCase
{
    private TestLogger   $logger;
    private MysqlSession $session_handler;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(MysqlSessionGarbageCollector::class, $this->newSubject());
    }

    public function test_it_collects_sessions_and_logs_count()
    {
        $this->session_handler
            ->expects($this->once())
            ->method('garbageCollect')
            ->willReturn(15);
        $this->newSubject()->execute();
        $this->assertTrue(
            $this->logger->hasInfoThatMatches('/^Garbage collected 15 sessions$/')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger          = new TestLogger;
        $this->session_handler = $this
            ->getMockBuilder(MysqlSession::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function newSubject(): MysqlSessionGarbageCollector
    {
        return new MysqlSessionGarbageCollector(
            $this->session_handler,
            $this->logger,
        );
    }

}

