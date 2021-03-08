<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\DependencyFactory\RequestExecutorFactory;
use Ingenerator\KohanaExtras\RequestExecutor\ContainerAwareRequestExecutor;

class RequestExecutorFactoryTest extends AbstractDependencyFactoryTest
{
    protected array $stub_services = [
        'dependencies' => ConstructorlessDependencyContainerStub::class,
    ];

    public function test_it_provides_container_aware_request_executor()
    {
        $service = $this->assertDefinesService(
            'kohana.request_executor',
            \Arr::merge(
                KohanaCoreFactory::definitions(),
                RequestExecutorFactory::definitions()
            )
        );
        $this->assertInstanceOf(ContainerAwareRequestExecutor::class, $service);
    }

}
