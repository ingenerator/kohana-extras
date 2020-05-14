<?php


namespace test\unit\Ingenerator\KohanaExtras\Logger;


use Ingenerator\KohanaExtras\Logger\KohanaPsrLogWriter;
use Ingenerator\PHPUtils\Logging\StackdriverApplicationLogger;
use Ingenerator\PHPUtils\Object\ConstantDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

class KohanaPsrLogWriterTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function test_it_is_initialisable_log_writer()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(KohanaPsrLogWriter::class, $subject);
        $this->assertInstanceOf(\Log_Writer::class, $subject);
    }

    public function provider_kohana_level_psr_level()
    {
        $psr_levels    = ConstantDirectory::forClass(LogLevel::class)->listConstants();
        $kohana_levels = ConstantDirectory::forClass(\Kohana_Log::class)->listConstants();
        $cases         = [];
        foreach ($kohana_levels as $level_name => $ko_level) {
            $cases[] = [$ko_level, $psr_levels[$level_name]];
        }

        return $cases;
    }

    /**
     * @dataProvider provider_kohana_level_psr_level
     */
    public function test_it_converts_kohana_log_levels_to_psr_levels($kohana_level, $expect_psr_level)
    {
        $this->newLogWithSubject()->add($kohana_level, 'I am a message');

        $record = $this->assertLoggedExactlyOnce();
        $this->assertSame($expect_psr_level, $record['level'], 'Expect correct log level');
        $this->assertSame('I am a message', $record['message'], 'Expect correct log message');
    }

    public function test_it_throws_on_unknown_level()
    {
        $log = $this->newLogWithSubject();
        $this->expectException(\InvalidArgumentException::class);
        $log->add('I am not a level', 'I am a message');
    }

    public function test_it_passes_through_log_time_in_context_params()
    {
        $this->newLogWithSubject()->add(\Log::INFO, 'This message at time');
        $record = $this->assertLoggedExactlyOnce();

        // Have to do it like this as php doesn't support createFromFormat with the RFC3339 format yet
        $at = new \DateTimeImmutable($record['context']['time']);
        $this->assertSame(
            $at->format(\DateTimeImmutable::RFC3339_EXTENDED),
            $record['context']['time'],
            'Expected RFC3339_EXTENDED date format'
        );
        $this->assertEqualsWithDelta(
            new \DateTimeImmutable,
            $at,
            1,
            'Expected log time to be just now'
        );
    }

    public function provider_context_params()
    {
        return [
            [['exception' => new \InvalidArgumentException('Anything')]],
            [['date' => new \DateTimeImmutable()]],
            [['custom_data' => ['user' => 'any', 'things' => TRUE]]],
        ];
    }

    /**
     * @dataProvider provider_context_params
     */
    public function test_it_passes_through_additional_context_in_context_params($additional)
    {
        $this->newLogWithSubject()->add(\Log::INFO, 'This is the message', [], $additional);

        $record = $this->assertLoggedExactlyOnce();
        $this->assertSame('This is the message', $record['message'], 'Does not modify message');
        unset($record['context']['time']);
        unset($record['context'][StackdriverApplicationLogger::PROP_SOURCE_LOCATION]);
        $this->assertSame($additional, $record['context']);
    }

    public function test_it_passes_through_source_location_as_external_call_to_kohana_log()
    {
        $this->newLogWithSubject()->add(\Log::INFO, 'This is the message', [], []);
        $expect_line = __LINE__ - 1;

        $record = $this->assertLoggedExactlyOnce();
        $this->assertSame(
            [
                'file'     => __FILE__,
                'line'     => $expect_line,
                'function' => __CLASS__.'->'.__FUNCTION__,
            ],
            $record['context'][StackdriverApplicationLogger::PROP_SOURCE_LOCATION]
        );
    }

    protected function setUp()
    {
        parent::setUp();
        $this->logger       = new TestLogger;
        \Log::$write_on_add = TRUE;
    }

    private function newSubject(): KohanaPsrLogWriter
    {
        return new KohanaPsrLogWriter($this->logger);
    }

    /**
     * @return \Kohana_Log
     */
    protected function newLogWithSubject(): \Kohana_Log
    {
        $log = new \Kohana_Log;
        $log->attach($this->newSubject());

        return $log;
    }

    protected function assertLoggedExactlyOnce(): array
    {
        $records = $this->logger->records;
        $this->assertCount(1, $records, 'Expected exactly one log entry');

        return \array_shift($records);
    }
}
