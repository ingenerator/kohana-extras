<?php


namespace Ingenerator\KohanaExtras\Routing;


/**
 * Use in unit tests etc to generate predictable URLs for assertions independent of actual route definitions
 */
class MockReverseRouter extends AbstractReverseRouter implements UrlReverseRouter
{

    public function getUrl(string $controller_class, array $params = [], array $query = []): string
    {
        ksort($params);
        $parts = [$controller_class];
        foreach ($this->stringifyAndValidateParams($params) as $k => $v) {
            $parts[] = $k.'='.$v;
        }

        if ($query) {
            ksort($query);
            $querystring = '?'.\http_build_query($query);
        } else {
            $querystring = '';
        }

        return '{routed:'.implode('/', $parts).$querystring.'}';
    }

}
