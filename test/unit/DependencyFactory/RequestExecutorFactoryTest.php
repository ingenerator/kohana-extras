<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\DependencyFactory\RequestExecutorFactory;
use Ingenerator\KohanaExtras\RequestExecutor\ContainerAwareRequestExecutor;

class RequestExecutorFactoryTest extends AbstractDependencyFactoryTest
{
    public function test_it_provides_container_aware_request_executor()
    {
        // NB: this isn't clean, need a way to assert behaviour of factories that depend on other services
        $service = $this->assertDefinesService(
            'kohana.request_executor',
            \Arr::merge(
                [
                    'dependencies' => [
                        '_settings' => [
                            'class'     => DependencyContainer::class,
                            'arguments' => [[]]
                        ]
                    ]
                ],
                KohanaCoreFactory::definitions(),
                RequestExecutorFactory::definitions()
            )
        );
        $this->assertInstanceOf(ContainerAwareRequestExecutor::class, $service);
    }

}
