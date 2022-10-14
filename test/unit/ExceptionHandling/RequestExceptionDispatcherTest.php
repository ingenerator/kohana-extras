<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\KohanaExtras\ExceptionHandling\ExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;
use Ingenerator\PHPUtils\Logging\StackdriverApplicationLogger;
use Ingenerator\PHPUtils\StringEncoding\JSON;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Process\Process;

class RequestExceptionDispatcherTest extends \PHPUnit\Framework\TestCase
{
    protected $default_handler;

    protected $dependencies;

    protected $logger;

    protected $handler_map = [];

    public function test_it_is_initialisable_when_kohana_is_all_initialised()
    {
        $this->assertInstanceOf(RequestExceptionDispatcher::class, $this->newSubject());
    }

    public function test_its_static_constructor_gets_instance_from_provided_di_container_when_initialised()
    {
        $this->assertInstanceOf(
            DummyContainerProvider::class,
            RequestExceptionDispatcher::factory(DummyContainerProvider::class, 'instance', 'dispatcher')
        );
    }

    public function provider_broken_constructor_args()
    {
        return [
            [
                [
                    'class'   => 'There\Is\No\Class\Called\That',
                    'method'  => 'instance',
                    'service' => 'dispatcher',
                ],
            ],
            [
                [
                    'class'   => DummyContainerProvider::class,
                    'method'  => 'iDontKnowWhatYouMean',
                    'service' => 'dispatcher',
                ],
            ],
            [
                [
                    'class'   => DummyContainerProvider::class,
                    'method'  => 'instance',
                    'service' => 'thereIsNoServiceCalledThat',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provider_broken_constructor_args
     */
    public function test_its_static_constructor_gets_basic_working_instance_when_container_not_initialised_or_invalid(
        $args
    ) {
        $this->withFakeFallbackLogger(
            function () use ($args) {
                $dispatcher = RequestExceptionDispatcher::factory($args['class'], $args['method'], $args['service']);
                $this->assertInstanceOf(RequestExceptionDispatcher::class, $dispatcher);
            }
        );
    }

    public function test_its_static_constructor_creates_instance_that_basically_works_even_without_kohana()
    {
        $basedir = \realpath(__DIR__.'/../../../');
        $proc    = $this->runTemporaryPhpScript(
            <<<'PHP'
                use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;
                require REQUEST_EXCEPTION_DISPATCHER_SOURCE;
                try {
                  ob_start();
                  echo "This should not make it to output";
                  throw new \InvalidArgumentException("Testing handling exceptions");
                } catch (\Exception $e) {
                  RequestExceptionDispatcher::dispatch($e);
                }
                fwrite(STDERR, "Code: ".http_response_code()."\n");
                // NB: can\'t test headers with CLI SAPI
PHP
            ,
            [
                'REQUEST_EXCEPTION_DISPATCHER_SOURCE' => $basedir.'/src/ExceptionHandling/RequestExceptionDispatcher.php',
            ]
        );

        $output = $proc->getOutput();
        $this->assertStringNotContainsString('This should not make it to output', $output);
        $this->assertStringContainsString('<div class="error-panel"', $output);
        $this->assertStringStartsWith('<!DOCTYPE', $output);
        $this->assertStringEndsWith('</html>', \trim($output));

        $error_output       = $proc->getErrorOutput();
        $error_output_lines = explode("\n", $error_output);
        $log                = JSON::decodeArray($error_output_lines[0]);
        $this->assertSame('EMERGENCY', $log['severity']);
        $this->assertStringContainsString('No container class Dependencies', $log['message']);

        $this->assertSame("Code: 500", $error_output_lines[2], 'Should set HTTP status');
    }

    public function test_its_static_constructor_creates_instance_that_works_with_default_stackdriver_logger_if_initialised(
    )
    {
        $basedir = \realpath(__DIR__.'/../../../');
        $proc    = $this->runTemporaryPhpScript(
            <<<'PHP'
            use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;
            use Ingenerator\PHPUtils\Logging\StackdriverApplicationLogger;
            require REQUEST_EXCEPTION_DISPATCHER_SOURCE;
            require COMPOSER_AUTOLOADER;
            StackdriverApplicationLogger::initialise(function () {
                return new StackdriverApplicationLogger(
                'php://stderr',
                ['custom_context' => 'from-test']
                );
            });
            RequestExceptionDispatcher::dispatch(new \BadMethodCallException('Anything happened'));            
PHP
            ,
            [
                'REQUEST_EXCEPTION_DISPATCHER_SOURCE' => $basedir.'/src/ExceptionHandling/RequestExceptionDispatcher.php',
                'COMPOSER_AUTOLOADER'                 => $basedir.'/vendor/autoload.php',
            ]
        );

        $output = $proc->getOutput();
        $this->assertStringContainsString('<div class="error-panel"', $output);
        $this->assertStringStartsWith('<!DOCTYPE', $output);
        $this->assertStringEndsWith('</html>', \trim($output));

        $error_output       = $proc->getErrorOutput();
        $error_output_lines = explode("\n", $error_output);
        $log                = JSON::decodeArray($error_output_lines[0]);
        $this->assertSame('EMERGENCY', $log['severity']);
        $this->assertStringContainsString('No container class Dependencies', $log['message']);
        $this->assertSame('from-test', $log['custom_context']);
    }

    public function provider_handler_map()
    {
        return [
            [
                // No handlers : expect default
                [],
                new \InvalidArgumentException('Anything'),
                'default: Anything',
            ],
            [
                // Single handler matches
                [
                    [
                        'type'    => \InvalidArgumentException::class,
                        'handler' => 'invalid_argument_handler',
                        'prefix'  => 'Invalid',
                    ],
                ],
                new \InvalidArgumentException('Whatever'),
                'Invalid: Whatever',
            ],
            [
                // First handler skips
                [
                    [
                        'type'    => \InvalidArgumentException::class,
                        'handler' => 'picky_handler',
                        'prefix'  => NULL,
                    ],
                    [
                        'type'    => \InvalidArgumentException::class,
                        'handler' => 'promiscuous_handler',
                        'prefix'  => 'Promiscuous',
                    ],
                ],
                new \InvalidArgumentException('Whatever'),
                'Promiscuous: Whatever',
            ],
            [
                // All handlers skip
                [
                    [
                        'type'    => \InvalidArgumentException::class,
                        'handler' => 'picky_handler',
                        'prefix'  => NULL,
                    ],
                    [
                        'type'    => \LogicException::class,
                        'handler' => 'still_picky_handler',
                        'prefix'  => NULL,
                    ],
                ],
                new \InvalidArgumentException('Whatever'),
                'default: Whatever',
            ],
            [
                // Class-specific handlers
                [
                    [
                        'type'    => \InvalidArgumentException::class,
                        'handler' => 'any_invalid',
                        'prefix'  => 'Invalid',
                    ],
                    [
                        'type'    => \LogicException::class,
                        'handler' => 'any_logic',
                        'prefix'  => 'Logic',
                    ],
                ],
                new \DomainException('Whatever'),
                'Logic: Whatever',
            ],
        ];
    }

    /**
     * @dataProvider provider_handler_map
     */
    public function test_it_returns_response_from_first_matching_handler_that_returns_a_response_or_the_default(
        $handlers,
        $e,
        $expect
    ) {
        $this->default_handler = new DummyHandler('default');
        $handler_map           = [];
        $services              = [];
        foreach ($handlers as $handler_def) {
            $handler_map[]                     = \Arr::extract($handler_def, ['type', 'handler']);
            $services[$handler_def['handler']] = [
                '_settings' => [
                    'class'     => DummyHandler::class,
                    'arguments' => [$handler_def['prefix']],
                ],
            ];
        }
        $this->handler_map  = $handler_map;
        $this->dependencies = new DependencyContainer($services);

        $response = $this->newSubject()->handle($e);
        $this->assertEquals($expect, $response->body());
    }

    public function test_it_logs_handler_exceptions_and_returns_generic_error()
    {
        $handler_e             = new \RuntimeException('The handler broke');
        $this->logger          = new TestLogger;
        $this->default_handler = new class($handler_e) implements ExceptionHandler {
            private $e;

            public function __construct($e) { $this->e = $e; }

            public function handle(\Throwable $e): ?\Response
            {
                throw $this->e;
            }
        };

        $app_e = new \InvalidArgumentException('The app broke');

        $response = $this->newSubject()->handle($app_e);
        $this->assertGenericErrorResponse($response);
        $this->assertSame(
            [
                [
                    'level'   => LogLevel::EMERGENCY,
                    'message' => '[RuntimeException] The handler broke ('.__FILE__.':'.$handler_e->getLine().')',
                    'context' => ['exception' => $handler_e],
                ],
                [
                    'level'   => LogLevel::EMERGENCY,
                    'message' => '[InvalidArgumentException] The app broke ('.__FILE__.':'.$app_e->getLine().')',
                    'context' => ['exception' => $app_e],
                ],
            ],
            $this->logger->records
        );
    }

    public function test_it_logs_and_returns_generic_error_with_missing_dependencies()
    {
        $exception = new \InvalidArgumentException('I broke');
        $logged    = $this->withFakeFallbackLogger(
            function () use ($exception) {
                $response = RequestExceptionDispatcher::factoryFallback()->handle($exception);
                $this->assertGenericErrorResponse($response);
            }
        );

        $this->assertSame(
            [
                [
                    'msg' => '[InvalidArgumentException] I broke ('.__FILE__.':'.$exception->getLine().')',
                    'ctx' => ['exception' => $exception],
                ],
            ],
            $logged
        );
    }

    public function test_it_logs_and_returns_generic_error_when_forced_to_create_fallback_handler()
    {
        $logged = $this->withFakeFallbackLogger(
            function () {
                $response = RequestExceptionDispatcher::factory('No\Such\Class', 'anything', 'anything')
                    ->handle(new \LogicException('No logic!'));
                $this->assertGenericErrorResponse($response);
            }
        );

        $this->assertCount(2, $logged, 'Expected two log messages');
        $this->assertStringContainsString('No\Such\Class', $logged[0]['msg']);
        $this->assertStringContainsString('No logic!', $logged[1]['msg']);
    }


    public function test_its_send_can_send_response_object()
    {
        $this->expectOutputString(':sadface:');
        RequestExceptionDispatcher::send(
            \Response::factory()
                ->status(400)
                ->headers('Content-Type', 'text/plain;charset=emoji')
                ->body(':sadface:')
        );
        // Can't assert headers as headers have already been sent
    }

    public function test_its_send_can_send_array_response()
    {
        $this->expectOutputString(':happyface:');
        RequestExceptionDispatcher::send(
            [
                'code'    => 419,
                'headers' => [
                    'Content-Type' => 'text/emoji',
                ],
                'body'    => ':happyface:',
            ]
        );
    }

    /**
     * @param string $script
     * @param array  $vars
     *
     * @return Process
     */
    protected function runTemporaryPhpScript(string $script, array $vars): Process
    {
        $script = \strtr(
            $script,
            array_map(function ($var) { return \var_export($var, TRUE); }, $vars)
        );

        $tmpfile = \tempnam(\sys_get_temp_dir(), 'exceptiontest');
        try {
            \file_put_contents($tmpfile, "<?php\n".$script);
            $proc = new Process(['php', $tmpfile]);
            $proc->run();
        } finally {
            \unlink($tmpfile);
        }

        return $proc;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->default_handler = new DummyHandler('default');
        $this->dependencies    = NULL;
        $this->logger          = new NullLogger;
    }

    protected function newSubject()
    {
        return new RequestExceptionDispatcher(
            $this->default_handler,
            $this->dependencies,
            $this->logger,
            $this->handler_map
        );
    }

    /**
     * @param $fn
     *
     * @return array
     */
    protected function withFakeFallbackLogger($fn)
    {
        $logger            = new class {
            public static $messages = [];

            public static function instance() { return new static; }

            public function emergency($message, array $context = [])
            {
                static::$messages[] = ['msg' => $message, 'ctx' => $context];
            }
        };
        $logger::$messages = [];
        $old_fallback_log  = RequestExceptionDispatcher::$fallbackLoggerFactories;
        try {
            RequestExceptionDispatcher::$fallbackLoggerFactories = [
                [\get_class($logger), 'instance'],
            ];
            $fn();
        } finally {
            RequestExceptionDispatcher::$fallbackLoggerFactories = $old_fallback_log;
        }

        return $logger::$messages;
    }

    protected function assertGenericErrorResponse($value)
    {
        $this->assertSame(500, $value['code']);
        $this->assertSame('text/html;charset=utf8', $value['headers']['Content-Type']);
        $this->assertStringContainsString('<body>', $value['body']);
    }

}

class DummyHandler implements ExceptionHandler
{
    /**
     * @var string
     */
    protected $response_prefix;

    public function __construct($response_prefix)
    {
        $this->response_prefix = $response_prefix;
    }

    public function handle(\Throwable $e): ?\Response
    {
        if ($this->response_prefix) {
            return \Response::factory()->body($this->response_prefix.': '.$e->getMessage());
        }

        return NULL;
    }
}

class DummyContainerProvider
{
    public static function instance()
    {
        return new static;
    }

    public function get($key)
    {
        if ($key === 'dispatcher') {
            return $this;
        }
        throw new \LogicException('No service '.$key);
    }


}
