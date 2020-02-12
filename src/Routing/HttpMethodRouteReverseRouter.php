<?php


namespace Ingenerator\KohanaExtras\Routing;

/**
 * Maps controllers and parameters back to URLs using HttpMethodRoutes defined in the app
 *
 */
class HttpMethodRouteReverseRouter extends AbstractReverseRouter implements UrlReverseRouter
{

    /**
     * @var string[]
     */
    protected $controller_name_map;

    /**
     * @var \Route[]
     */
    protected $controller_route_map;

    /**
     * @var \Ingenerator\KohanaExtras\Routing\HttpMethodRoute[]
     */
    protected $http_method_routes = [];

    /**
     *
     * @param \Route[] $routes Note: Only HttpMethodRoute instances will actually be considered
     */
    public function __construct(array $routes)
    {
        foreach ($routes as $route) {
            if ($route instanceof HttpMethodRoute) {
                $this->http_method_routes[] = $route;
            } elseif ( ! $route instanceof \Route) {
                throw new \InvalidArgumentException(
                    __CLASS__.' expected array of routes, got '.\gettype($route)
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl(string $controller_class, array $params = [], array $query = []): string
    {
        if ( ! $this->controller_route_map) {
            $this->compileControllerRouteMap();
        }

        // Routes are defined with a leading \ on the controller class in HttpMethodRoute
        $controller_class = '\\'.ltrim($controller_class, '\\');

        if ( ! isset($this->controller_route_map[$controller_class])) {
            throw ReverseRoutingException::noRouteToController($controller_class);
        }

        $params = $this->stringifyAndValidateParams($params);
        $params['controller'] = $this->controller_name_map[$controller_class];
        $url = \URL::site(
            $this->controller_route_map[$controller_class]->uri($params)
        );

        if ( ! empty($query)) {
            $url .= '?'.\http_build_query($query);
        }

        return $url;
    }

    protected function compileControllerRouteMap()
    {
        $this->controller_route_map = [];
        $this->controller_name_map  = [];

        foreach ($this->http_method_routes as $route) {
            foreach ($route->listActionClasses() as $url_name => $class) {
                if (isset($this->controller_route_map[$class])) {
                    throw ReverseRoutingException::multipleRoutesToController($class);
                }
                $this->controller_route_map[$class] = $route;
                $this->controller_name_map[$class]  = $url_name;
            }
        }
    }

}
