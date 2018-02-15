<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\DependencyFactory\KohanaCoreFactory;

class KohanaCoreFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_provides_log()
    {
        $this->assertSame(\Kohana::$log, KohanaCoreFactory::getLog());
    }

    public function test_it_provides_session()
    {
        $this->assertSame(\Session::instance(), KohanaCoreFactory::getSession());
    }

    public function test_it_provides_kohana_initial_request()
    {
        $this->assertSame(\Request::initial(), KohanaCoreFactory::getRequest());
    }

}
