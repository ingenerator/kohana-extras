<?php


namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\ExceptionHandling\FallbackLogger;
use Ingenerator\PHPUtils\StringEncoding\JSON;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use function file_get_contents;
use function get_class;
use function uniqid;

class FallbackLoggerTest extends TestCase
{

    public function provider_factory_providers()
    {
        $valid = new class {
            public static function make()
            {
                return new static;
            }

            public static function throwStuff()
            {
                throw new RuntimeException('That was a mistake');
            }

            public function notStatic()
            {
                return new static;
            }

            public function emergency() { }
        };

        $invalid     = new class {
            public static function make()
            {
                return new static;
            }


        };
        $valid_class = get_class($valid);

        return [
            [
                // Simple case, one logger and it exists
                [[$valid_class, 'make']],
                $valid_class,
            ],
            [
                // First is invalid, getter method does not exist
                [
                    [$valid_class, 'unknown_method'],
                    [$valid_class, 'make'],
                ],
                $valid_class,
            ],
            [
                // First is invalid, class does not exist
                [
                    [uniqid('invalid-class'), 'unknown_method'],
                    [$valid_class, 'make'],
                ],
                $valid_class,
            ],
            [
                // First is invalid, getter throws
                [
                    [$valid_class, 'throwStuff'],
                    [$valid_class, 'make'],
                ],
                $valid_class,
            ],
            [
                // First is invalid, getter is not static
                [
                    [$valid_class, 'notStatic'],
                    [$valid_class, 'make'],
                ],
                $valid_class,
            ],
            [
                // First is invalid, no emergency method
                [
                    [get_class($invalid), 'make'],
                    [$valid_class, 'make'],
                ],
                $valid_class,
            ],
            [
                // First is invalid, not an array of class->method
                [
                    'anything:whatever',
                    [$valid_class, 'make'],
                ],
                $valid_class,
            ],
        ];

    }

    /**
     * @dataProvider provider_factory_providers
     */
    public function test_its_factory_returns_first_object_that_does_not_fail(array $factories, string $expect_class)
    {
        $logger = FallbackLogger::makeBestLogger($factories);
        $this->assertInstanceOf($expect_class, $logger);
    }

    public function provider_invalid_factories()
    {
        return [
            [NULL],
            [[]],
            [['all junk']],
            ['anything'],
        ];
    }

    /**
     * @dataProvider provider_invalid_factories
     */
    public function test_its_factory_returns_instance_of_fallback_logger_if_all_else_fails($factories)
    {
        $logger = FallbackLogger::makeBestLogger($factories);
        $this->assertInstanceOf(FallbackLogger::class, $logger);
    }

    public function provider_log_entries()
    {
        $e  = new InvalidArgumentException('Stuff went wrong');
        $e2 = new RuntimeException('Other stuff happened', 15, $e);

        return [
            [
                'I am an error',
                ['exception' => $e2],
                [
                    'severity'  => 'EMERGENCY',
                    'message'   => 'I am an error',
                    'exception' => [
                        'class'    => RuntimeException::class,
                        'msg'      => 'Other stuff happened',
                        'code'     => 15,
                        'file'     => $e2->getFile(),
                        'line'     => $e2->getLine(),
                        'previous' => [
                            'class' => InvalidArgumentException::class,
                            'msg'   => 'Stuff went wrong',
                            'code'  => 0,
                            'file'  => $e->getFile(),
                            'line'  => $e->getLine(),
                        ],

                    ],
                ],
            ],
            [
                'Whatever',
                ['exception' => $e],
                [
                    'severity'  => 'EMERGENCY',
                    'message'   => 'Whatever',
                    'exception' => [
                        'class' => InvalidArgumentException::class,
                        'msg'   => 'Stuff went wrong',
                        'code'  => 0,
                        'file'  => $e->getFile(),
                        'line'  => $e->getLine(),

                    ],
                ],
            ],
            [
                'Da shiz',
                ['exception' => new stdClass],
                [
                    'severity'  => 'EMERGENCY',
                    'message'   => 'Da shiz',
                    'exception' => [],
                ],
            ],
            [
                'Nah ah',
                [],
                [
                    'severity' => 'EMERGENCY',
                    'message'  => 'Nah ah',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider_log_entries
     */
    public function test_it_logs_emergency_as_basic_json_to_provided_stream($msg, $context, $expect)
    {
        $vfs        = vfsStream::setup();
        $log_stream = $vfs->url().'/log.txt';
        $logger     = new FallbackLogger($log_stream);
        $logger->emergency($msg, $context);
        $logged = file_get_contents($log_stream);
        $this->assertStringEndsWith("\n", $logged);
        $this->assertSame($expect, JSON::decodeArray($logged));
    }

    public function test_it_appends_to_existing_file()
    {
        $vfs        = vfsStream::setup();
        $log_stream = vfsStream::newFile('log.txt')->withContent("existing line\n")->at($vfs)->url();
        $logger     = new FallbackLogger($log_stream);
        $logger->emergency('Anything');
        $logged = file_get_contents($log_stream);
        $this->assertStringStartsWith("existing line\n", $logged);
        $this->assertCount(3, explode("\n", $logged));
    }

}
