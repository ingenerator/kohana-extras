<?php


namespace Ingenerator\KohanaExtras\Routing;


abstract class AbstractReverseRouter
{

    /**
     * @param array $params
     *
     * @return array
     */
    protected function stringifyAndValidateParams(array $params): array
    {
        foreach ($params as $param => $param_value) {
            if ($param_value instanceof URLIdentifiableEntity) {
                $params[$param] = $param_value->getURLId();
            } else {
                if (\is_string($param_value) OR \is_int($param_value)) {
                    $params[$param] = $param_value;
                } else {
                    throw ReverseRoutingException::invalidParameterType($param, $param_value);
                }
            }
        }

        return $params;
    }

}
