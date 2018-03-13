<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\KohanaExtras\DependencyFactory\ExceptionHandlerFactory;
use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\ExceptionHandling\DefaultRequestExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;

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

        $this->assertEquals(
            [
                [
                    'type'    => \InvalidArgumentException::class,
                    'handler' => 'exception_handler.handler.My\InvalidArgumentHandler',
                ],
                [
                    'type'    => \RuntimeException::class,
                    'handler' => 'exception_handler.handler.My\RuntimeHandler',
                ],
            ],
            $definitions['exception_handler']['dispatcher']['_settings']['arguments'][2],
            'Should build mapping for the dispatcher'
        );

        $this->assertEquals(
            [
                '_settings' => [
                    'class'     => 'My\InvalidArgumentHandler',
                    'arguments' => ['%kohana.log%'],
                ],
            ],
            $definitions['exception_handler']['handler']['My\InvalidArgumentHandler'],
            'Should define each handler as a service in its own right'
        );
    }


}
