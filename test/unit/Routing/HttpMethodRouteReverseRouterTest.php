<?php


namespace test\unit\Ingenerator\KohanaExtras\Routing;

use Ingenerator\KohanaExtras\Routing\HttpMethodRoute;
use Ingenerator\KohanaExtras\Routing\HttpMethodRouteReverseRouter;
use Ingenerator\KohanaExtras\Routing\ReverseRoutingException;
use Ingenerator\KohanaExtras\Routing\UrlReverseRouter;
use PHPUnit\Framework\TestCase;
use test\mock\Ingenerator\KohanaExtras\Routing\UrlIdentifiableStringStub;


class HttpMethodRouteReverseRouterTest extends TestCase
{
    protected $routes = [];

    public function test_it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(HttpMethodRouteReverseRouter::class, $subject);
        $this->assertInstanceOf(UrlReverseRouter::class, $subject);
    }

    public function test_it_throws_if_any_routes_provided_are_not_routes()
    {
        $this->routes = ['junk'];
        $this->expectException(\InvalidArgumentException::class);
        $this->newSubject();
    }

    public function test_its_get_url_method_throws_if_no_route_to_controller()
    {
        $this->routes = [
            new HttpMethodRoute(
                'home/my-controller',
                ['Controller\Anything\SomeController']
            ),
        ];
        $subject      = $this->newSubject();
        $this->expectException(ReverseRoutingException::class);
        $this->expectExceptionMessage('No route defined to controller');
        $subject->getURL('Controller\Anything');
    }

    public function test_it_ignores_legacy_kohana_routes()
    {
        // In theory this route matches the requested controller, but it is very challenging
        // to do the runtime parsing to work out which route would actually match due to wildcard
        // and order-of-definition matching. You want the new behaviour, define new HTTPMethodRoutes
        $this->routes = [
            (new \Route('home/<controller>'))->defaults(['controller' => 'Controller\DoStuff']),
        ];
        $subject      = $this->newSubject();
        $this->expectException(ReverseRoutingException::class);
        $this->expectExceptionMessage('No route defined to controller');
        $subject->getURL('Controller\DoStuff');
    }


    public function test_its_get_url_method_throws_if_multiple_routes_to_controller()
    {
        $this->routes = [
            new HttpMethodRoute(
                'home/my-controller',
                ['Controller\MyController']
            ),
            new HttpMethodRoute(
                'home/my-controller-legacy-path',
                ['Controller\MyController']
            ),
        ];
        $subject      = $this->newSubject();
        $this->expectException(ReverseRoutingException::class);
        $this->expectExceptionMessage('Multiple routes defined to controller');
        $subject->getURL('Controller\MyController');
    }

    public function provider_controllers_with_no_params()
    {
        return [
            [
                [
                    new HttpMethodRoute(
                        'home/explicit-controller',
                        ['Controller\ExplicitDoSomething']
                    ),
                ],
                'Controller\ExplicitDoSomething',
                \URL::site('home/explicit-controller'),
            ],
            [
                [
                    new HttpMethodRoute(
                        'anywhere/<controller>',
                        [
                            'Controller\ExplicitDoSomethingController',
                            'Controller\Whatever\WelcomeController',
                        ]
                    ),
                ],
                'Controller\Whatever\WelcomeController',
                \URL::site('anywhere/welcome'),
            ],
            [
                [
                    new HttpMethodRoute(
                        'anywhere/<controller>',
                        [
                            'Controller\ExplicitDoSomethingController',
                            'Controller\Whatever\WelcomeController',
                        ]
                    ),
                    new HttpMethodRoute(
                        '<controller>',
                        [
                            'Controller\RootControllerOne',
                            'Controller\Whatever\RootControllerTwo',
                            'Controller\Whatever\RootControllerThree',
                        ]
                    ),
                ],
                'Controller\Whatever\RootControllerTwo',
                \URL::site('root-controller-two'),
            ],
        ];
    }

    /**
     * @dataProvider provider_controllers_with_no_params
     */
    public function test_its_get_url_method_returns_url_for_controller_with_no_params(
        $routes,
        $controller,
        $expect_url
    ) {
        $this->routes = $routes;
        $this->assertSame($expect_url, $this->newSubject()->getUrl($controller));
    }

    public function test_its_get_url_method_returns_url_for_controller_with_required_params()
    {
        $this->routes = [
            new HttpMethodRoute(
                'anywhere/<controller>/<id>',
                [
                    'Controller\ExplicitDoSomethingController',
                ]
            ),
        ];
        $this->assertSame(
            \URL::site('anywhere/explicit-do-something/1939'),
            $this->newSubject()->getUrl('Controller\ExplicitDoSomethingController', ['id' => 1939])
        );
    }

    /**
     * @testWith [{"slug": "my-file"}, "any/file/my-file-current"]
     *           [{"slug": "my-file", "format": "png"}, "any/file/my-file-current.png"]
     *           [{"slug": "my-file", "format": "png", "version": "custom"}, "any/file/my-file-custom.png"]
     */
    public function test_its_get_url_method_returns_url_with_or_without_optional_params(
        $params,
        $expect
    ) {
        $this->routes = [
            (new HttpMethodRoute(
                'any/<controller>/<slug>-<version>(.<format>)',
                [
                    'Controller\Download\FileController',
                ]
            ))->defaults(['version' => 'current']),
        ];

        $this->assertSame(
            \URL::site($expect),
            $this->newSubject()->getUrl('Controller\Download\FileController', $params)
        );
    }

    public function provider_url_identifiable_entities()
    {
        return [
            [
                ['slug' => 'hardcoded', 'id' => new UrlIdentifiableStringStub(19238)],
                \URL::site('do-thing/19238-hardcoded'),
            ],
            [
                ['slug' => new UrlIdentifiableStringStub('this-is-the-slug'), 'id' => 19482],
                \URL::site('do-thing/19482-this-is-the-slug'),
            ],
            [
                [
                    'slug' => new UrlIdentifiableStringStub('this-is-the-slug'),
                    'id'   => new UrlIdentifiableStringStub('this-is-id'),
                ],
                \URL::site('do-thing/this-is-id-this-is-the-slug'),
            ],
        ];
    }

    /**
     * @dataProvider provider_url_identifiable_entities
     */
    public function test_its_get_url_method_can_convert_url_identifiable_identity_param_to_id(
        array $params,
        string $expect
    ) {
        $this->routes = [
            new HttpMethodRoute(
                '<controller>/<id>-<slug>',
                [
                    'Controller\DoThingController',
                ]
            ),
        ];

        $this->assertSame(
            $expect,
            $this->newSubject()->getUrl('Controller\DoThingController', $params)
        );
    }

    public function provider_invalid_params()
    {
        return [
            [new \DateTimeImmutable],
            [new \stdClass],
            [TRUE],
        ];
    }

    /**
     * @dataProvider provider_invalid_params
     */
    public function test_its_get_url_method_throws_if_any_param_not_scalar_or_url_identifiable(
        $invalid
    ) {
        $this->routes = [
            new HttpMethodRoute(
                '<invalid_param>',
                [
                    'Controller\DoThingController',
                ]
            ),
        ];
        $subject      = $this->newSubject();
        $this->expectException(ReverseRoutingException::class);
        $this->expectExceptionMessage('Invalid parameter type');
        $subject->getUrl('Controller\DoThingController', ['invalid_param' => $invalid]);
    }

    public function test_its_get_url_method_optionally_encodes_querystring()
    {
        $this->routes = [
            new HttpMethodRoute(
                'anywhere/<controller>/<id>',
                [
                    'Controller\ExplicitDoSomethingController',
                ]
            ),
        ];
        $this->assertSame(
            \URL::site('anywhere/explicit-do-something/1939?filter_by=date&filter=today'),
            $this->newSubject()->getUrl(
                'Controller\ExplicitDoSomethingController',
                ['id' => 1939],
                ['filter_by' => 'date', 'filter' => 'today']
            )
        );
    }

    protected function newSubject()
    {
        return new HttpMethodRouteReverseRouter($this->routes);
    }
}
