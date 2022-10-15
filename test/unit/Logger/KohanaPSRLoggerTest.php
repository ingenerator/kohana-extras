<?php

namespace test\unit\Ingenerator\KohanaExtras;

use Exception;
use Ingenerator\KohanaExtras\Logger\KohanaPSRLogger;
use InvalidArgumentException;
use Kohana_Exception;
use Log;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use const PHP_EOL;

class KohanaPSRLoggerTest extends TestCase
{

    private Log $log;

    protected function setUp(): void
    {
        parent::setUp();
        $this->log = new class extends Log {
            public array $added = [];

            public function add($level, $message, array $values = NULL, array $additional = NULL)
            {
                $this->added[] = get_defined_vars();
            }
        };
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
        $this->assertCount(1, $this->log->added, "should have logged once");
    }

    public function test_it_logs_debug_messages()
    {
        $this->newSubject()->debug('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::DEBUG,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_info_messages()
    {
        $this->newSubject()->info('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::INFO,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_notice_messages()
    {
        $this->newSubject()->notice('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::NOTICE,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_warning_messages()
    {
        $this->newSubject()->warning('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::WARNING,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_error_messages()
    {
        $this->newSubject()->error('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::ERROR,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_critical_messages()
    {
        $this->newSubject()->critical('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::CRITICAL,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_alert_messages()
    {
        $this->newSubject()->alert('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::ALERT,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_logs_emergency_messages()
    {
        $this->newSubject()->emergency('some message');
        $this->assertSame(
            [
                [
                    'level'      => Log::EMERGENCY,
                    'message'    => 'some message',
                    'values'     => [],
                    'additional' => [],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_passes_context_as_additional()
    {
        $e = new stdClass;
        $this->newSubject()->info('Something that we caught', ['anything' => $e]);
        $this->assertSame(
            [
                [
                    'level'      => Log::INFO,
                    'message'    => 'Something that we caught',
                    'values'     => [],
                    'additional' => ['anything' => $e],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_appends_exception_type_and_message_to_log_message()
    {
        $e = new Exception('Foo');
        $this->newSubject()->info('We handled this', ['exception' => $e]);
        $this->assertSame(
            [
                [
                    'level'      => Log::INFO,
                    'message'    => 'We handled this'.PHP_EOL.Kohana_Exception::text($e),
                    'values'     => [],
                    'additional' => ['exception' => $e],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_copes_if_context_exception_is_not_exception_instance()
    {
        $this->newSubject()->info('Problem', ['exception' => 'Uh-oh - this is not an exception']);
        $this->assertSame(
            [
                [
                    'level'      => Log::INFO,
                    'message'    => 'Problem',
                    'values'     => [],
                    'additional' => ['exception' => 'Uh-oh - this is not an exception'],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_can_accept_exception_as_message()
    {
        $e = new Exception('Problem');
        $this->newSubject()->alert($e);
        $this->assertSame(
            [
                [
                    'level'      => Log::ALERT,
                    'message'    => Kohana_Exception::text($e),
                    'values'     => [],
                    'additional' => ['exception' => $e],
                ],
            ],
            $this->log->added
        );
    }

    public function test_it_throws_on_invalid_level()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->newSubject()->log('random', 'bad level');
    }

    protected function newSubject(): KohanaPSRLogger
    {
        return new KohanaPSRLogger($this->log);
    }

}
