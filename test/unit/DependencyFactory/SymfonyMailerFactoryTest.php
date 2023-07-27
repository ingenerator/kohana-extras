<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\SymfonyMailerFactory;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;


class SymfonyMailerFactoryTest extends AbstractDependencyFactoryTest
{
    public function test_it_defines_symfonymailer_mailer_smtp()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    Mailer::class,
                    $this->assertDefinesService('symfonymailer.mailer', SymfonyMailerFactory::definitionsSMTP())
                );
            }
        );
    }

    public function test_it_defines_symfonymailer_smtp_transport()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    EsmtpTransport::class,
                    $this->assertDefinesService('symfonymailer.transport', SymfonyMailerFactory::definitionsSMTP())
                );
            }
        );
    }

}
