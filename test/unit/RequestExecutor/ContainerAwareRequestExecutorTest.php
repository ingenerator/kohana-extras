<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 */

namespace test\unit\Ingenerator\KohanaExtras\RequestExecutor;

use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Ingenerator\KohanaExtras\RequestExecutor\ContainerAwareRequestExecutor;
use Request;
use Response;

class ContainerAwareRequestExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Route[]
     */
    protected $routes = [];

    /**
     * @var DependencyContainer
     */
    protected $dependencies;

    public function test_it_is_initialisable_request_executor()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(ContainerAwareRequestExecutor::class, $subject);
        $this->assertInstanceOf(\Request_Executor::class, $subject);
    }

    public function test_it_executes_default_controller_if_nothing_registered_in_container()
    {
        $this->routes = [
            (new \Route(''))->defaults(
                ['controller' => '\\'.ArbitraryController::class]
            )
        ];
        $this->assertSame(
            'actual: '.ArbitraryController::class.' rq: \\'.ArbitraryController::class,
            $this->newSubject()->execute(\Request::with(['uri' => '']))->body()
        );
    }

    public function test_it_executes_controller_from_container_if_registered()
    {
        $this->routes = [
            (new \Route(''))->defaults(['controller' => '\Any\Controller\Class'])
        ];

        $this->dependencies = new DependencyContainer(
            [
                'controller' => [
                    '\Any\Controller\Class' => ['_settings' => ['class' => ArbitraryController::class]]
                ]
            ]
        );


        $this->assertSame(
            'actual: '.ArbitraryController::class.' rq: \Any\Controller\Class',
            $this->newSubject()->execute(\Request::with(['uri' => '']))->body()
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->dependencies = new DependencyContainer([]);
    }

    protected function newSubject()
    {
        return new ContainerAwareRequestExecutor($this->routes, $this->dependencies);
    }

}

class ArbitraryController extends \Controller
{
    public function execute()
    {
        return $this->response->body('actual: '.static::class.' rq: '.$this->request->controller());
    }
}
