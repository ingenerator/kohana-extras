<?php


namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyFactory\GarbageCollectorFactory;
use Ingenerator\KohanaExtras\GarbageCollector\GarbageCollectionController;
use Ingenerator\KohanaExtras\GarbageCollector\GarbageCollector;
use Ingenerator\KohanaExtras\GarbageCollector\MysqlSessionGarbageCollector;
use Ingenerator\PHPUtils\Object\ObjectPropertyRipper;
use Psr\Log\NullLogger;
use test\mock\Ingenerator\KohanaExtras\Session\MysqlSessionStub;

class GarbageCollectorFactoryTest extends AbstractDependencyFactoryTest
{
    protected array $stub_services = [
        'kohana.psr_log'  => NullLogger::class,
        'session_handler' => MysqlSessionStub::class,
    ];

    protected array $stub_config = [
        'application' => [
            'garbage_collection_token' => 'sesame',
        ],
    ];

    public function test_it_defines_the_gc_controller()
    {
        $this->assertInstanceOf(
            GarbageCollectionController::class,
            $this->assertDefinesService(
                'controller.\\'.GarbageCollectionController::class,
                GarbageCollectorFactory::definitions()
            )
        );
    }

    public function test_it_configures_the_mysql_session_garbage_collector_by_default()
    {
        $this->assertCreatesGCControllerWithCollectorClasses(
            [MysqlSessionGarbageCollector::class],
            GarbageCollectorFactory::definitions()
        );
    }

    public function test_applications_can_disable_the_sessions_collector()
    {
        $this->assertCreatesGCControllerWithCollectorClasses(
            [],
            GarbageCollectorFactory::definitions([MysqlSessionGarbageCollector::class => FALSE])
        );
    }

    public function test_applications_can_include_additional_collectors()
    {
        $this->stub_services['some_random_dep'] = NullLogger::class;
        $this->assertCreatesGCControllerWithCollectorClasses(
            [MysqlSessionGarbageCollector::class, CustomCollector::class],
            GarbageCollectorFactory::definitions(
                [
                    CustomCollector::class => ['%some_random_dep%'],
                ]
            )
        );
    }

    /**
     * @param array  $definitions
     * @param string $expect_Coll
     */
    protected function assertCreatesGCControllerWithCollectorClasses(array $expect_classes, array $definitions): void
    {
        $controller = $this->assertDefinesService(
            'controller.\\'.GarbageCollectionController::class,
            $definitions
        );


        $actual = \array_map(
            fn($coll) => \get_class($coll),
            ObjectPropertyRipper::ripOne($controller, 'collectors')
        );
        $this->assertSame($expect_classes, $actual);
    }

}

class CustomCollector implements GarbageCollector
{
    public function __construct(NullLogger $logger) { }

    public function execute(): void { }
}
