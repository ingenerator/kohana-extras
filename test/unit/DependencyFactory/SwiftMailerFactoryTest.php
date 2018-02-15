<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\DoctrineFactory;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\ORM\EntityManager;
use Ingenerator\KohanaExtras\DependencyFactory\SwiftMailerFactory;

class SwiftMailerFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_entity_manager()
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

    public function test_it_defines_raw_pdo_connection()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_SendmailTransport::class,
                    $this->assertDefinesService('swiftmailer.transport', SwiftMailerFactory::definitions())
                );
            }
        );
    }

}
