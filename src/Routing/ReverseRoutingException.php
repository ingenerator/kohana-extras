<?php


namespace Ingenerator\KohanaExtras\Routing;


class ReverseRoutingException extends \InvalidArgumentException
{

    public static function noRouteToController(string $controller_class)
    {
        return new static('No route defined to controller `'.$controller_class.'`');
    }

    public static function multipleRoutesToController(string $controller_class)
    {
        return new static('Multiple routes defined to controller `'.$controller_class.'`');
    }

    public static function invalidParameterType($param, $param_value)
    {
        if (is_object($param_value)) {
            $type = \get_class($param_value);
        } else {
            $type = \gettype($param_value);
        }

        return new static (
            sprintf(
                'Invalid parameter type for `%s` (%s) - expected string, int or URLIdentifiableEntity',
                $param,
                $type
            )
        );
    }
}
