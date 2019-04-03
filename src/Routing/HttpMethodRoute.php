<?php
/**
 * Defines Route\HttpMethodRoute
 */

namespace Ingenerator\KohanaExtras\Routing;

use Request;
use Route;

/**
 * Extension to standard Kohana routing that:
 *
 *  * routes to a specific set of whitelisted action classes (one action per HTTP URL) in a
 *    predictable way.
 *  * set the `action` based on the HTTP method
 *
 * Unsupported HTTP actions will throw a 404 error.
 */
class HttpMethodRoute extends \Route
{
    /**
     * @var string[]
     */
    protected $action_classes;

    /**
     * Call ::create instead to actually create and store a URL
     *
     * @param string   $uri
     * @param string[] $action_classes
     * @param string[] $param_patterns
     */
    public function __construct($uri, array $action_classes, array $param_patterns = [])
    {
        foreach (['<action>', '<directory>'] as $param) {
            if (\strpos($uri, $param) !== FALSE) {
                throw new \InvalidArgumentException(
                    'Unexpected '.$param.' parameter in route URL: '.$uri
                );
            }
        }

        if (empty($action_classes)) {
            throw new \InvalidArgumentException(
                'At least one action class must be specified for '.__CLASS__
            );
        }

        $this->action_classes = [];
        foreach ($action_classes as $classname) {
            if (\substr($classname, 0, 1) !== '\\') {
                $classname = '\\'.$classname;
            }

            $controller                        = $this->getControllerNameFromClass($classname);
            $this->action_classes[$controller] = $classname;
        }

        if ( ! isset($param_patterns['id'])) {
            $param_patterns['id'] = '\d+';
        }

        parent::__construct($uri, $param_patterns);
    }

    /**
     * @param string $classname
     *
     * @return string
     */
    protected function getControllerNameFromClass($classname)
    {
        $classname  = \preg_replace('/Controller$/', '', $classname);
        $parts      = \explode('\\', $classname);
        $class      = \array_pop($parts);
        $controller = \strtolower(
            \preg_replace('/(?<!^)([A-Z]+)/', '-\\1', $class)
        );

        return $controller;
    }

    /**
     * Create a route, store it and return it.
     *
     * @param   string $name           route name
     * @param   string $uri            URI pattern
     * @param string[] $action_classes fully qualified names of classes this should match
     * @param string[] $param_patterns custom regex patterns for extra url parameters
     *
     * @return static
     */
    public static function create($name, $uri, array $action_classes, array $param_patterns = [])
    {
        return Route::$_routes[$name] = new static($uri, $action_classes, $param_patterns);
    }

    /**
     * Create a route that matches a single action class with an explicit URL (e.g. without a <controller> param
     *
     * For example:
     *
     *   HttpMethodRoute::createExplicit('custom', 'some/url/we/like/<with_any_param>', MyStrangelyNamedController::class);
     *
     * @param string   $name
     * @param string   $uri
     * @param string   $action_class
     * @param string[] $param_patterns
     *
     * @return static
     */
    public static function createExplicit($name, $uri, $action_class, array $param_patterns = [])
    {
        $route = static::create($name, $uri, [$action_class], $param_patterns);
        $route->defaults(['controller' => $route->getControllerNameFromClass($action_class)]);

        return $route;
    }

    /**
     * @deprecated see the create method instead as the signature is different
     */
    public static function set($name, $uri = NULL, $regex = NULL)
    {
        throw new \BadMethodCallException('Use '.__CLASS__.'::create instead of set');
    }

    /**
     * {@inheritdoc}
     */
    public function matches(Request $request)
    {
        if ( ! $result = parent::matches($request)) {
            return FALSE;
        }

        $controller = \strtolower(\Arr::get($result, 'controller'));

        if ( ! isset($this->action_classes[$controller])) {
            return FALSE;
        }

        $result['controller'] = $this->action_classes[$controller];

        $request_method = \trim(\strtolower($request->method()));
        if ($request_method === 'head') {
            $request_method = 'get';
        }

        $result['action'] = $request_method;


        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function uri(array $params = NULL)
    {
        if ($controller = \Arr::get($params, 'controller')) {
            if ( ! isset($this->action_classes[$controller])) {
                throw new \OutOfBoundsException(
                    'Undefined controller `'.$controller.'` specified for route URL'.\json_encode(
                        $this->action_classes
                    )
                );
            }
        }

        return parent::uri($params);
    }


}
