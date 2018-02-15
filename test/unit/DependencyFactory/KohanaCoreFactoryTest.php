<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;

class KohanaCoreFactoryTest extends AbstractDependencyFactoryTest
{
    public function test_it_provides_log()
    {
        $service = $this->assertDefinesService('kohana.log', KohanaCoreFactory::definitions());
        $this->assertSame(\Kohana::$log, $service);
    }

    public function test_it_provides_session()
    {
        $service = $this->assertDefinesService('kohana.session', KohanaCoreFactory::definitions());
        $this->assertSame(\Session::instance(), $service);
    }

    public function test_it_provides_kohana_initial_request()
    {
        $original_request = \Request::$initial;
        try {
            \Request::$initial = new \Request('anything');
            $service           = $this->assertDefinesService('kohana.request', KohanaCoreFactory::definitions());
            $this->assertSame(\Request::initial(), $service);
        } finally {
            \Request::$initial = $original_request;
        }
    }

}
