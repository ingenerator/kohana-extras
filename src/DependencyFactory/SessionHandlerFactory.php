<?php


namespace Ingenerator\KohanaExtras\DependencyFactory;


class SessionHandlerFactory
{
    const OPT_PDO_SERVICE = 'pdo_service_key';

    /**
     * Define the session handler service
     *
     * By default uses the `doctrine.pdo_connection` service as the required PDO dependency - this is published by
     * Ingenerator\KohanaDoctrine\Dependency\DoctrineFactory. You can use an alternate PDO dependency by declaring
     * it in your project dependencies and passing the key in the options e.g.
     *
     *   SessionHandlerFactory::definitions([SessionHandlerFactory::OPT_PDO_SERVICE => 'some.arbitrary.pdo']);
     *
     *
     * @param array $options
     * @return \array[][]
     */
    public static function definitions(array $options = []): array
    {
        $options = \array_merge(
            [
                static::OPT_PDO_SERVICE => 'doctrine.pdo_connection'
            ],
            $options
        );
        return [
            'session_handler' => [
                '_settings' => [
                    'class' => \Ingenerator\PHPUtils\Session\MysqlSession::class,
                    'arguments' => [
                        '%'.$options[static::OPT_PDO_SERVICE].'%',
                        '@!application.session_hash_salt!@',
                    ],
                ],
            ],
        ];
    }
}
