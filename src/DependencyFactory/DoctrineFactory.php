<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Driver\PDOConnection;
use Ingenerator\KohanaExtras\DependencyContainer\DependencyContainer;

class DoctrineFactory extends OptionalDependencyFactory
{

    /**
     * @param array $services (optional) array of services
     *
     * @return array
     */
    public static function definitions($services = ['subscribers' => []])
    {
        static::requireClass(\Doctrine_EMFactory::class, 'ingenerator/kohana-doctrine2');
        static::requireClass(EntityManager::class, 'doctrine/orm');

        return [
            'doctrine' => [
                'entity_manager' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildEntityManager',
                        'arguments' => [
                            '%dependencies%',
                            $services['subscribers']
                        ],
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

    public static function buildEntityManager(DependencyContainer $dependencies, array $subscribers)
    {
        $factory = new \Doctrine_EMFactory(\Kohana::$config, \Kohana::$environment);
        $em      = $factory->entity_manager();

        foreach ($subscribers as $subscriber) {
            $em->getEventManager()->addEventSubscriber($dependencies->get($subscriber));
        }

        return $em;
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
