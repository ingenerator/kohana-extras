<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\Routing;


use test\mock\Kohana\Request\HtmlRequestStub;
use test\unit\BaseTestCase;
use Ingenerator\KohanaExtras\Routing\HttpMethodRoute;

class HttpMethodRouteTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(HttpMethodRoute::class, $this->newSubject());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function test_its_static_set_throws()
    {
        HttpMethodRoute::set('any');
    }

    public function test_its_static_set_creates_instance_and_adds_to_the_global_routes_collection()
    {
        $name     = uniqid('http-method-route-test');
        $instance = HttpMethodRoute::create(
            $name,
            'entity/<controller>(.<format>)',
            [static::class],
            ['format' => 'csv']
        );
        $this->assertInstanceOf(HttpMethodRoute::class, $instance);
        $this->assertInstanceOf(\Route::class, $instance);
        $this->assertSame($instance, \Route::get($name));
    }


    /**
     * @expectedException \InvalidArgumentException
     * @testWith ["stuff/<controller>/<action>"]
     *           ["<directory>/<controller>"]
     */
    public function test_it_cannot_be_constructed_with_unsupported_url_parameters($uri)
    {
        new HttpMethodRoute($uri, [static::class]);
    }

    public function test_it_cannot_be_constructed_with_missing_controller_parameter()
    {
        new HttpMethodRoute('foo/bar', [static::class]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_it_cannot_be_constructed_without_at_least_one_action_class()
    {
        new HttpMethodRoute('foo/<controller>', []);
    }

    /**
     * @testWith ["foo/<controller>", ["Action\\Entity\\DoThings"], "bar/bax", false]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "foo", false]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "foo/", false]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "foo/DoThings", false]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "foo/do-other", false]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "bar/do-things", false]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "foo/do-things", true]
     *           ["foo/<controller>", ["Action\\Entity\\DoThings"], "foo/do-things", true]
     *           ["foo/<controller>", ["Action\\Entity\\DoThingPDF"], "foo/do-thing-pdf", true]
     *           ["baz/<controller>", ["\\Entity\\DoThings", "\\Entity\\DoStuff"], "baz/do-stuff", true]
     *           ["web/stuff/<controller>", ["\\Entity\\DoThings", "\\Entity\\DoStuff"], "web/stuff/do-stuff", true]
     */
    public function test_it_only_matches_if_pattern_matches_and_controller_whitelisted(
        $uri_pattern,
        $actions,
        $url,
        $expect_match
    ) {
        $route   = new HttpMethodRoute($uri_pattern, $actions);
        $matches = $route->matches(\Request::with(['uri' => $url]));
        if ($expect_match) {
            $this->assertInternalType('array', $matches);
            $this->assertNotEmpty($matches);
        } else {
            $this->assertFalse($matches);
        }
    }

    public function test_it_does_not_return_directory_param_in_match()
    {
        $route   = new HttpMethodRoute('foo/<controller>', ['Some\Controller\ActionClass']);
        $matches = $route->matches(\Request::with(['uri' => 'foo/action-class']));
        $this->assertArrayNotHasKey('directory', $matches);
    }

    /**
     * @testWith [["Action\\Entity\\DoThings"], "do-things", "\\Action\\Entity\\DoThings"]
     *           [["\\Action\\Entity\\DoThings"], "do-things", "\\Action\\Entity\\DoThings"]
     *           [["Action\\Entity\\DoThings", "Other\\ControllerAction"], "controller-action", "\\Other\\ControllerAction"]
     */
    public function test_it_returns_fully_qualified_controller_name_as_controller(
        $actions,
        $url,
        $expect
    ) {
        $route   = new HttpMethodRoute('<controller>', $actions);
        $matches = $route->matches(\Request::with(['uri' => $url]));
        $this->assertSame($expect, $matches['controller']);
    }

    /**
     * @testWith ["HEAD", "get"]
     *           ["GET", "get"]
     *           ["POST", "post"]
     *           ["PUT", "put"]
     */
    public function test_it_returns_http_method_as_action($method, $expect)
    {
        $route   = new HttpMethodRoute('<controller>', ['Some\\Controller']);
        $request = \Request::with(['uri' => 'controller', 'method' => $method]);
        $this->assertSame($expect, $route->matches($request)['action']);
    }

    /**
     * @testWith ["foo/<id>/<controller>", "foo/12/controller", true, "12"]
     *           ["foo/<controller>/<id>", "foo/controller/93", true, "93"]
     *           ["foo/<controller>/<id>", "foo/controller/abc", false, null]
     */
    public function test_it_optionally_matches_id_param_as_decimal_string_by_default(
        $uri_pattern,
        $url,
        $expect_match,
        $expect_id
    ) {
        $route   = new HttpMethodRoute($uri_pattern, ['Some\\Controller']);
        $matches = $route->matches(\Request::with(['uri' => $url]));

        if ($expect_match) {
            $this->assertSame($expect_id, $matches['id']);
        } else {
            $this->assertFalse($matches);
        }
    }

    public function test_it_optionally_accepts_custom_id_format()
    {
        $route   = new HttpMethodRoute(
            'foo/<id>/<controller>', ['Some\\Controller'], ['id' => '.+']
        );
        $matches = $route->matches(\Request::with(['uri' => 'foo/an/id/controller']));
        $this->assertSame('an/id', $matches['id']);
    }

    /**
     * @testWith ["foo/<controller>/bar", ["Some\\ActionClass"], {"controller" : "action-class"}, "foo/action-class/bar"]
     *           ["foo/<controller>/<id>", ["Some\\ActionClass"], {"controller" : "action-class", "id": "12"}, "foo/action-class/12"]
     *           ["foo/<controller>/<id>", ["Some\\ActionPDF"], {"controller" : "action-pdf", "id": "12"}, "foo/action-pdf/12"]
     */
    public function test_it_generates_correct_uri_with_valid_controller_url_part(
        $url,
        $actions,
        $params,
        $expect
    ) {
        $route = new HttpMethodRoute($url, $actions);
        $this->assertEquals(
            $expect,
            $route->uri($params)
        );
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function test_it_throws_if_attempting_to_generate_uri_with_undefined_controller()
    {
        $route = new HttpMethodRoute("foo/<controller>", ['Some\ActionClass']);
        $route->uri(['controller' => 'nothing-here']);
    }

    protected function newSubject()
    {
        return new HttpMethodRoute('foo/<controller>', [static::class]);
    }

}
