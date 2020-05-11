<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\Message\KohanaMessageProvider;
use Ingenerator\PHPUtils\Object\ObjectPropertyRipper;
use Psr\Log\LoggerInterface;

class KohanaCoreFactoryTest extends AbstractDependencyFactoryTest
{
    public function test_it_provides_log()
    {
        $service = $this->assertDefinesService('kohana.log', KohanaCoreFactory::definitions());
        $this->assertSame(\Kohana::$log, $service);
    }

    public function test_it_provides_stackdriver_application_logger_as_default_psr_log()
    {
        $service = $this->assertDefinesService('kohana.psr_log', KohanaCoreFactory::definitions());
        $this->assertInstanceOf(LoggerInterface::class, $service);
        $this->assertSame(\Kohana::$log, ObjectPropertyRipper::ripOne($service, 'log'));
    }

    public function test_it_provides_session()
    {
        $service = $this->assertDefinesService('kohana.session', KohanaCoreFactory::definitions());
        $this->assertSame(\Session::instance(), $service);
    }

    public function test_it_provides_kohana_initial_request()
    {
        $original_request = \Request::$initial;
        try {
            \Request::$initial = \Request::with(['uri' => 'anything']);
            $service           = $this->assertDefinesService('kohana.request', KohanaCoreFactory::definitions());
            $this->assertSame(\Request::initial(), $service);
        } finally {
            \Request::$initial = $original_request;
        }
    }

    public function test_it_provides_kohana_message_provider()
    {
        $service = $this->assertDefinesService('kohana.message_provider', KohanaCoreFactory::definitions());
        $this->assertInstanceOf(KohanaMessageProvider::class, $service);
    }

    public function test_it_provides_routes()
    {
        $route  = \Route::set('test-factory-route', '/path/to/factory/test/to/not/match/anything');
        $routes = $this->assertDefinesService('kohana.routes', KohanaCoreFactory::definitions());
        $this->assertSame($route, $routes['test-factory-route']);
    }

}
