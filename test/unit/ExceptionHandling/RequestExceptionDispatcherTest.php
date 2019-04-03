<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\KohanaExtras\ExceptionHandling\ExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;
use Symfony\Component\Process\Process;

class RequestExceptionDispatcherTest extends \PHPUnit\Framework\TestCase
{
    protected $default_handler;
    protected $dependencies;
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
        $dispatcher = RequestExceptionDispatcher::factory($args['class'], $args['method'], $args['service']);
        $this->assertInstanceOf(RequestExceptionDispatcher::class, $dispatcher);
    }

    public function test_its_static_constructor_creates_instance_that_basically_works_even_without_kohana()
    {
        $file   = \realpath(__DIR__.'/../../../src/ExceptionHandling/RequestExceptionDispatcher.php');
        $class  = RequestExceptionDispatcher::class;
        $script = \implode(
            "\n",
            [
                '<?php',
                'require '.\var_export($file, TRUE).';',
                'try {',
                '  ob_start();',
                '  echo "This should not make it to output";',
                '  throw new \InvalidArgumentException("Testing handling exceptions");',
                '} catch (\Exception $e) {',
                '  '.$class.'::dispatch($e);',
                '}',
                '',
                'fwrite(STDERR, "Code: ".http_response_code()."\n");',
                '// NB: can\'t test headers with CLI SAPI',
            ]
        );

        $tmpfile = \tempnam(\sys_get_temp_dir(), 'exceptiontest');
        try {
            \file_put_contents($tmpfile, $script);
            $proc = new Process('php '.$tmpfile);
            $proc->run();
            $output = $proc->getOutput();
            $this->assertNotContains('This should not make it to output', $output);
            $this->assertContains('<div class="error-panel"', $output);
            $this->assertStringStartsWith('<!DOCTYPE', $output);
            $this->assertStringEndsWith('</html>', \trim($output));
            $this->assertSame("Code: 500\n", $proc->getErrorOutput(), 'Should set HTTP status');
        } finally {
            \unlink($tmpfile);
        }
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

    public function test_it_logs_handler_exceptions_to_syslog_and_returns_generic_error()
    {
        $logged = $this->withFakeSyslog(
            function () {
                $this->default_handler = new ThrowingHandler('The handler broke');
                $response              = $this->newSubject()->handle(new \InvalidArgumentException('The app broke'));
                $this->assertGenericErrorResponse($response);
            }
        );

        $this->assertContains('The app broke', $logged);
        $this->assertContains('The handler broke', $logged);
    }

    public function test_it_logs_to_syslog_and_returns_generic_error_with_missing_dependencies()
    {
        $logged = $this->withFakeSyslog(
            function () {
                $response = RequestExceptionDispatcher::factoryFallback()->handle(
                    new \InvalidArgumentException('I broke')
                );
                $this->assertGenericErrorResponse($response);
            }
        );
        $this->assertContains('I broke', $logged);
    }

    public function test_it_logs_to_syslog_and_returns_generic_error_when_forced_to_create_fallback_handler()
    {
        $logged = $this->withFakeSyslog(
            function () {
                $response = RequestExceptionDispatcher::factory('No\Such\Class', 'anything', 'anything')
                    ->handle(new \LogicException('No logic!'));
                $this->assertGenericErrorResponse($response);
            }
        );

        $this->assertContains('No\Such\Class', $logged);
        $this->assertContains('No logic!', $logged);
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

    public function setUp()
    {
        parent::setUp();
        $this->default_handler = new DummyHandler('default');
        $this->dependencies    = NULL;
    }

    protected function newSubject()
    {
        return new RequestExceptionDispatcher($this->default_handler, $this->dependencies, $this->handler_map);
    }

    /**
     * @param $fn
     *
     * @return array
     */
    protected function withFakeSyslog($fn)
    {
        $logged = [];
        try {
            RequestExceptionDispatcher::$syslog_func = function ($priority, $message) use (&$logged) {
                $logged[] = $priority.' - '.$message;
            };

            $fn();
        } finally {
            RequestExceptionDispatcher::$syslog_func = 'syslog';
        }

        return \implode("\n", $logged);
    }

    protected function assertGenericErrorResponse($value)
    {
        $this->assertSame(500, $value['code']);
        $this->assertSame('text/html;charset=utf8', $value['headers']['Content-Type']);
        $this->assertContains('<body>', $value['body']);
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

    public function handle($e)
    {
        if ($this->response_prefix) {
            return \Response::factory()->body($this->response_prefix.': '.$e->getMessage());
        }

        return NULL;
    }
}

class ThrowingHandler implements ExceptionHandler
{

    /**
     * @var string
     */
    protected $msg;

    public function __construct($msg = 'I broke') { $this->msg = $msg; }

    public function handle($e)
    {
        throw new \RuntimeException($this->msg, 0, $e);
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
