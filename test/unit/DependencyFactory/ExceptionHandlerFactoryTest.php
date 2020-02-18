<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Doctrine\DBAL\Exception\ConnectionException;
use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\KohanaExtras\DependencyFactory\ExceptionHandlerFactory;
use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\ExceptionHandling\DBALConnectionExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\DefaultRequestExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;
use Ingenerator\KohanaExtras\ExceptionHandling\SessionExceptionHandler;
use Ingenerator\PHPUtils\StringEncoding\JSON;

class ExceptionHandlerFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_provides_default_handler()
    {
        $service = $this->assertDefinesService(
            'exception_handler.default',
            \Arr::merge(
                KohanaCoreFactory::definitions(),
                ExceptionHandlerFactory::definitions()
            )
        );
        $this->assertInstanceOf(DefaultRequestExceptionHandler::class, $service);
    }

    public function test_it_provides_exception_dispatcher_at_expected_default_service_name()
    {
        $service = $this->assertDefinesService(
            'exception_handler.dispatcher',
            \Arr::merge(
                [
                    'dependencies' => [
                        '_settings' => [
                            'class'     => DependencyContainer::class,
                            'arguments' => [[]],
                        ],
                    ],
                ],
                KohanaCoreFactory::definitions(),
                ExceptionHandlerFactory::definitions()
            )
        );

        $this->assertInstanceOf(RequestExceptionDispatcher::class, $service);
    }

    public function test_it_provides_defined_handlers_and_the_mapping_config()
    {
        $definitions = ExceptionHandlerFactory::definitions(
            [
                [
                    'type'    => \InvalidArgumentException::class,
                    'handler' => [
                        'class'     => 'My\InvalidArgumentHandler',
                        'arguments' => ['%kohana.log%'],
                    ],
                ],
                [
                    'type'    => \RuntimeException::class,
                    'handler' => [
                        'class'     => 'My\RuntimeHandler',
                        'arguments' => ['%kohana.request%'],
                    ],
                ],
            ]
        );

        $this->assertDefinesExceptionHandlerMappingAndServiceAtIndex(
            \InvalidArgumentException::class,
            \My\InvalidArgumentHandler::class,
            ['%kohana.log%'],
            0,
            $definitions
        );

        $this->assertDefinesExceptionHandlerMappingAndServiceAtIndex(
            \RuntimeException::class,
            \My\RuntimeHandler::class,
            ['%kohana.request%'],
            1,
            $definitions
        );
    }

    protected function assertDefinesExceptionHandlerMappingAndServiceAtIndex(
        string $exception_class,
        string $handler_class,
        array $handler_args,
        int $dispatcher_index,
        array $definitions
    ) {
        $defined_handler_mapping = $definitions['exception_handler']['dispatcher']['_settings']['arguments'][2];
        $defined_handlers        = implode(
            "\n",
            array_map(
                function ($map) { return $map['type'].' => '.$map['handler']; },
                $defined_handler_mapping
            )
        );

        $this->assertSame(
            [
                'type'    => $exception_class,
                'handler' => 'exception_handler.handler.'.$handler_class,
            ],
            $defined_handler_mapping[$dispatcher_index],
            "Expected handler mapping at correct index - got:\n".$defined_handlers
        );

        $this->assertSame(
            ['_settings' => ['class' => $handler_class, 'arguments' => $handler_args]],
            $definitions['exception_handler']['handler'][$handler_class]
        );
    }

    public function test_it_defines_default_handlers_for_some_exceptions()
    {
        $definitions = ExceptionHandlerFactory::definitions();

        $this->assertDefinesExceptionHandlerMappingAndServiceAtIndex(
            ConnectionException::class,
            DBALConnectionExceptionHandler::class,
            ['%kohana.log%'],
            0,
            $definitions
        );

        $this->assertDefinesExceptionHandlerMappingAndServiceAtIndex(
            \Session_Exception::class,
            SessionExceptionHandler::class,
            ['%kohana.log%'],
            1,
            $definitions
        );
    }

    public function test_default_handlers_can_be_disabled()
    {
        $definitions = ExceptionHandlerFactory::definitions(
            [
                [
                    'type'    => ConnectionException::class,
                    'handler' => FALSE,
                ],
            ]
        );

        $this->assertDoesNotDefineDispatcherMappingForException(
            ConnectionException::class,
            $definitions
        );

        $this->assertDoesNotDefineHandlerService(
            DBALConnectionExceptionHandler::class,
            $definitions
        );
    }

    public function test_default_handlers_can_be_replaced()
    {
        $definitions = ExceptionHandlerFactory::definitions(
            [
                [
                    'type'    => ConnectionException::class,
                    'handler' => [
                        'class'     => \My\OwnConnectionExceptionHandler::class,
                        'arguments' => ['%custom.service%'],
                    ],
                ],
            ]
        );

        $this->assertDefinesExceptionHandlerMappingAndServiceAtIndex(
            ConnectionException::class,
            \My\OwnConnectionExceptionHandler::class,
            ['%custom.service%'],
            0,
            $definitions
        );

        $this->assertDoesNotDefineHandlerService(
            DBALConnectionExceptionHandler::class,
            $definitions
        );
    }

    public function test_default_handlers_can_be_changed_in_sequence()
    {
        $definitions = ExceptionHandlerFactory::definitions(
            [
                [
                    'type'    => \InvalidArgumentException::class,
                    'handler' => [
                        'class'     => \InvalidArgumentExceptionHandler::class,
                        'arguments' => ['%kohana.log'],
                    ],
                ],
                [
                    'type'    => ConnectionException::class,
                    'handler' => [
                        'class'     => \My\OwnConnectionExceptionHandler::class,
                        'arguments' => ['%custom.service%'],
                    ],
                ],
                [
                    'type'    => OtherException::class,
                    'handler' => [
                        'class'     => \My\OtherExceptionHandler::class,
                        'arguments' => ['%kohana.request%'],
                    ],
                ],
            ]
        );

        $this->assertDefinesExactHandlerMapping(
            [
                \InvalidArgumentException::class => \InvalidArgumentExceptionHandler::class,
                ConnectionException::class       => \My\OwnConnectionExceptionHandler::class,
                OtherException::class            => \My\OtherExceptionHandler::class,
                // Note that only Session_Exception gets appended from defaults, connection is handled above
                \Session_Exception::class        => SessionExceptionHandler::class,
            ],
            $definitions
        );
    }

    protected function assertDefinesExactHandlerMapping(array $expect_type_map, array $definitions)
    {
        $expect = [];
        foreach ($expect_type_map as $e_class => $h_class) {
            $expect[] = ['type' => $e_class, 'handler' => 'exception_handler.handler.'.$h_class];
        }

        $this->assertSame(
            $expect,
            $definitions['exception_handler']['dispatcher']['_settings']['arguments'][2],
            'Expected exact exception to handler defition map'
        );
    }

    /**
     * @param string $exception_class
     * @param array  $definitions
     */
    protected function assertDoesNotDefineDispatcherMappingForException(
        string $exception_class,
        array $definitions
    ): void {
        foreach ($definitions['exception_handler']['dispatcher']['_settings']['arguments'][2] as $handler_def) {
            if ($handler_def['type'] === $exception_class) {
                $this->fail(
                    "Did not expect to find a handler definition for $exception_class\n"
                    ."Got: ".JSON::encode($handler_def)
                );
            }
        }
    }

    /**
     * @param string $class
     * @param array  $definitions
     */
    protected function assertDoesNotDefineHandlerService(string $class, array $definitions): void
    {
        $this->assertArrayNotHasKey(
            $class,
            $definitions['exception_handler']['handler'],
            'Should not define handler service'
        );
    }


}
