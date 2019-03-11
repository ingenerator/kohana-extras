<?php

namespace test\unit\Ingenerator\KohanaExtras;

use Ingenerator\KohanaExtras\Logger\KohanaPSRLogger;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class KohanaPSRLoggerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Log
     */
    protected $log;


    protected function setUp()
    {
        parent::setUp();
        $this->log = $this->prophesize(\Log::class);;
    }

    public function test_it_is_initializable()
    {
        $this->assertInstanceOf(KohanaPSRLogger::class, $this->newSubject());
    }

    public function test_it_is_a_psr3_logger()
    {
        $this->assertInstanceOf(LoggerInterface::class, $this->newSubject());
    }

    public function test_it_logs_to_a_provided_kohana_log()
    {
        $this->newSubject()->info('Some message');
        $this->log->add(Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_debug_messages()
    {
        $this->newSubject()->debug('some message');
        $this->log->add(\Log::DEBUG, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_info_messages()
    {
        $this->newSubject()->info('some message');
        $this->log->add(\Log::INFO, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_notice_messages()
    {
        $this->newSubject()->notice('some message');
        $this->log->add(\Log::NOTICE, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_warning_messages()
    {
        $this->newSubject()->warning('some message');
        $this->log->add(\Log::WARNING, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_error_messages()
    {
        $this->newSubject()->error('some message');
        $this->log->add(\Log::ERROR, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_critical_messages()
    {
        $this->newSubject()->critical('some message');
        $this->log->add(\Log::CRITICAL, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_alert_messages()
    {
        $this->newSubject()->alert('some message');
        $this->log->add(\Log::ALERT, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_logs_emergency_messages()
    {
        $this->newSubject()->emergency('some message');
        $this->log->add(\Log::EMERGENCY, 'some message', Argument::cetera())->shouldHaveBeenCalled();
    }

    public function test_it_passes_context_as_additional()
    {
        $e = new \Exception('Foo');
        $this->newSubject()->info('Something that we caught', ['exception' => $e]);
        $this->log->add(\Log::INFO, Argument::type('string'), [], ['exception' => $e])->shouldHaveBeenCalled();
    }

    public function test_it_appends_exception_type_and_message_to_log_message()
    {
        $e = new \Exception('Foo');
        $this->newSubject()->info('We handled this', ['exception' => $e]);
        $this->log->add(\Log::INFO, 'We handled this'.\PHP_EOL.\Kohana_Exception::text($e), Argument::cetera())
            ->shouldHaveBeenCalled();
    }

    public function test_it_copes_if_context_exception_is_not_exception_instance()
    {
        $this->newSubject()->info('Problem', ['exception' => 'Uh-oh - this is not an exception']);
        $this->assertTrue(TRUE, 'Did not throw exception');
    }

    public function test_it_can_accept_exception_as_message()
    {
        $e = new \Exception('Problem');
        $this->newSubject()->alert($e);
        $this->log->add(\Log::ALERT, \Kohana_Exception::text($e), [], ['exception' => $e])->shouldHaveBeenCalled();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_throws_on_invalid_level()
    {
        $this->newSubject()->log('random', 'bad level');
    }

    /**
     * @return KohanaPSRLogger
     */
    protected function newSubject()
    {
        return new KohanaPSRLogger($this->log->reveal());
    }

}
