<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\SessionHandlerFactory;
use Ingenerator\PHPUtils\Session\MysqlSession;

class SessionHandlerFactoryTest extends AbstractDependencyFactoryTest
{
    protected array $stub_services = [
        'doctrine.pdo_connection' => PDOStub::class,
    ];

    protected array $stub_config = [
        'application' => [
            'session_hash_salt' => 'insecure',
        ],
    ];

    public function test_it_defines_session_handler_with_doctrine_pdo_as_default()
    {
        $this->assertInstanceOf(
            MysqlSession::class,
            $this->assertDefinesService(
                'session_handler',
                SessionHandlerFactory::definitions()
            )
        );
    }

    public function test_it_defines_session_handler_with_alternate_pdo_service_ref()
    {
        $this->stub_services['my.random_pdo'] = PDOStub::class;
        $this->assertInstanceOf(
            MysqlSession::class,
            $this->assertDefinesService(
                'session_handler',
                SessionHandlerFactory::definitions(
                    [
                        SessionHandlerFactory::OPT_PDO_SERVICE => 'my.random_pdo',
                    ]
                )
            )
        );
    }

}

class PDOStub extends \PDO
{
    public function __construct()
    {
    }
}
