<?php


namespace test\unit\Ingenerator\KohanaExtras\Routing;


use Ingenerator\KohanaExtras\Routing\MockReverseRouter;
use Ingenerator\KohanaExtras\Routing\UrlReverseRouter;
use PHPUnit\Framework\TestCase;
use test\mock\Ingenerator\KohanaExtras\Routing\UrlIdentifiableStringStub;

class MockReverseRouterTest extends TestCase
{

    public function it_is_initialisable()
    {
        $subject = $this->newSubject();
        $this->assertInstanceOf(MockReverseRouter::class, $subject);
        $this->assertInstanceOf(UrlReverseRouter::class, $subject);
    }

    public function test_its_get_url_returns_controller_class_with_prefix()
    {
        $this->assertSame(
            '{routed:Controller\My\ActionController}',
            $this->newSubject()->getUrl('Controller\My\ActionController')
        );
    }

    public function test_its_get_url_includes_all_provided_params_in_alpha_order()
    {
        $this->assertSame(
            '{routed:Controller\My\ActionController/id=15/slug=some-stuff}',
            $this->newSubject()->getUrl(
                'Controller\My\ActionController',
                ['slug' => 'some-stuff', 'id' => 15]
            )
        );
    }

    public function test_its_get_url_converts_url_identifiable_entities()
    {
        $this->assertSame(
            '{routed:Controller\My\ActionController/slug=entity-slug}',
            $this->newSubject()->getUrl(
                'Controller\My\ActionController',
                ['slug' => new UrlIdentifiableStringStub('entity-slug')]
            )
        );
    }

    public function test_its_get_url_includes_querystring_in_alpha_order()
    {
        $this->assertSame(
            '{routed:Controller_Anything/id=15?date=today&version_from=123}',
            $this->newSubject()->getUrl(
                'Controller_Anything',
                ['id' => 15],
                ['version_from' => 123, 'date' => 'today']
            )
        );
    }

    protected function newSubject()
    {
        return new MockReverseRouter;
    }
}

