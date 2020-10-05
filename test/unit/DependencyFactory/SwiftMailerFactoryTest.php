<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\SwiftMailerFactory;

class SwiftMailerFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_swiftmailer_mailer()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_Mailer::class,
                    $this->assertDefinesService('swiftmailer.mailer', SwiftMailerFactory::definitions())
                );
            }
        );
    }

    public function test_it_defines_swiftmailer_transport()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_SmtpTransport::class,
                    $this->assertDefinesService('swiftmailer.transport', SwiftMailerFactory::definitions())
                );
            }
        );
    }

}
