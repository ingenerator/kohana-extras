<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\DoctrineFactory;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\ORM\EntityManager;

class DoctrineFactoryTest extends AbstractDependencyFactoryTest
{

    public function test_it_defines_entity_manager()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    EntityManager::class,
                    $this->assertDefinesService('doctrine.entity_manager', DoctrineFactory::definitions())
                );
            }
        );
    }

    public function test_it_defines_raw_pdo_connection()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    PDOConnection::class,
                    $this->assertDefinesService('doctrine.pdo_connection', DoctrineFactory::definitions())
                );
            }
        );
    }

}
