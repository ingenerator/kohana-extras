<?php

namespace Ingenerator\KohanaExtras\RequestExecutor;

use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;
use Request;
use Response;

/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 */
class ContainerAwareRequestExecutor extends \Request_Executor
{
    /**
     * @var \Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer
     */
    protected $dependencies;

    /**
     * @param \Route[] $routes
     */
    public function __construct($routes, DependencyContainer $dependencies)
    {
        parent::__construct($routes);
        $this->dependencies = $dependencies;
    }

    /**
     * @param \Request  $request
     * @param \Response $response
     *
     * @return \Controller
     * @throws \HTTP_Exception
     * @throws \Kohana_Exception
     */
    protected function create_controller(Request $request, Response $response)
    {
        $service = 'controller.'.$request->controller();
        // Note this only really works with explicit, namespace-named, controllers
        // or generally with routes that are case-sensitive for their controller name
        if ($this->dependencies->has($service)) {
            return $this->dependencies->get($service);
        }

        return parent::create_controller($request, $response);
    }

}
