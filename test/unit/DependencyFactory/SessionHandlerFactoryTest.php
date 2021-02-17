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
    public function test_it_defines_session_handler_with_doctrine_pdo_as_default()
    {
        $this->assertInstanceOf(
            MysqlSession::class,
            $this->assertDefinesService(
                'session_handler',
                \Arr::merge(
                    [
                        'doctrine' => [
                            'pdo_connection' => [
                                '_settings' => [
                                    'class' => PDOStub::class
                                ],
                            ],
                        ],
                    ],
                    SessionHandlerFactory::definitions()
                )
            )
        );
    }

    public function test_it_defines_session_handler_with_alternate_pdo_service_ref()
    {
        $this->assertInstanceOf(
            MysqlSession::class,
            $this->assertDefinesService(
                'session_handler',
                \Arr::merge(
                    [
                        'my' => [
                            'random_pdo' => [
                                '_settings' => [
                                    'class' => PDOStub::class,
                                ],
                            ]
                        ],
                    ],
                    SessionHandlerFactory::definitions(
                        [
                            SessionHandlerFactory::OPT_PDO_SERVICE => 'my.random_pdo'
                        ]
                    )
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
