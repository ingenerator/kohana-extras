<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\TokenistaFactory;

class TokenistaFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_tokenista_factory()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Tokenista::class,
                    $this->assertDefinesService('tokenista.tokenista', TokenistaFactory::definitions())
                );
            }
        );
    }


}
