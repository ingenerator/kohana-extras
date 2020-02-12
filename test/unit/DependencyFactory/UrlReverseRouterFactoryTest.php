<?php

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;
use Ingenerator\KohanaExtras\DependencyFactory\UrlReverseRouterFactory;
use Ingenerator\KohanaExtras\Routing\HttpMethodRouteReverseRouter;

class UrlReverseRouterFactoryTest extends AbstractDependencyFactoryTest
{
    public function test_it_provides_http_method_route_reverse_router()
    {

        $service = $this->assertDefinesService(
            'util.url_reverse_router',
            \Arr::merge(
                KohanaCoreFactory::definitions(),
                UrlReverseRouterFactory::definitions()
            )
        );
        $this->assertInstanceOf(HttpMethodRouteReverseRouter::class, $service);
    }
}
