<?php


namespace Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\GarbageCollector\GarbageCollectionController;
use Ingenerator\KohanaExtras\GarbageCollector\MysqlSessionGarbageCollector;

class GarbageCollectorFactory
{
    private static $default_collectors = [
        MysqlSessionGarbageCollector::class => ['%session_handler%', '%kohana.psr_log%'],
    ];

    /**
     * Defines the GarbageCollectionController service and all the collectors it should execute.
     *
     * By default this will include the MysqlSessionGarbageCollector but you can disable this by:
     *
     *   GarbageCollectorFactory::definitions([MysqlSessionGarbageCollector::class => FALSE]);
     *
     * You can also register custom collector classes by:
     *
     *   GarbageCollectorFactory::definitions([
     *     MyCustomCollector::class => [
     *       // The arguments for the custom collector, in dependencies format e.g.:
     *       '@application.my_custom_collection_lifetime@',
     *       '%repository.things%
     *     ]
     *   ]);
     *
     * @param array $collectors
     *
     * @return array
     */
    public static function definitions(array $collectors = []): array
    {
        $collectors      = array_merge(static::$default_collectors, $collectors);
        $controller_args = [
            '@application.garbage_collection_token@',
        ];
        $defs            = [];
        $collector_idx   = 0;
        foreach ($collectors as $collector_class => $arguments) {
            if ($arguments === FALSE) {
                // User has disabled this collector
                continue;
            }

            $defs['garbage_collection']['collectors'][$collector_idx] = [
                '_settings' => [
                    'class'     => $collector_class,
                    'arguments' => $arguments,
                ],
            ];

            $controller_args[] = '%garbage_collection.collectors.'.$collector_idx.'%';
            $collector_idx++;
        }

        return array_merge(
            $defs,
            RequestExecutorFactory::controllerDefinitions(
                [
                    GarbageCollectionController::class => $controller_args,
                ]
            )
        );
    }
}
