<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Driver\PDOConnection;

class DoctrineFactory extends OptionalDependencyFactory
{

    public static function definitions()
    {
        static::requireClass(\Doctrine_EMFactory::class, 'ingenerator/kohana-doctrine2');
        static::requireClass(EntityManager::class, 'doctrine/orm');

        return [
            'doctrine' => [
                'entity_manager' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildEntityManager',
                        'shared'      => TRUE,
                    ],
                ],
                'pdo_connection' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'getRawPDO',
                        'arguments'   => [
                            '%doctrine.entity_manager%',
                        ],
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }

    public static function buildEntityManager()
    {
        $factory = new \Doctrine_EMFactory(\Kohana::$config, \Kohana::$environment);

        return $factory->entity_manager();
    }

    public static function getRawPDO(EntityManager $entityManager)
    {
        $driver = $entityManager->getConnection()->getWrappedConnection();
        if ( ! $driver instanceof PDOConnection) {
            throw new \InvalidArgumentException(
                'Expected Doctrine connection to be instance of '.\PDO::class.', got '.get_class($driver)
            );
        }

        return $driver;
    }
}
