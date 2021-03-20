<?php


namespace Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\PHPUtils\Monitoring\NullMetricsAgent;
use Ingenerator\PHPUtils\Monitoring\OperationTimer;


class MetricsAgentFactory
{
    private const TIMER = [
                        'class'     => OperationTimer::class,
                        'arguments' => [
                            '%metrics.agent%'
                        ]];

    /**
     * Defines a NullMetricsAgent
     *
     * For services which require a metrics agent included in projects where metrics aren't recorded or required
     */
    public static function definitionsNullAgent(): array
    {
        return [
            'metrics' => [
                'agent' => [
                    '_settings' => [
                        'class'     => NullMetricsAgent::class,
                        'arguments' => [],
                    ],
                ],
                'timer' => [
                    '_settings' => self::TIMER,
                ]
            ],
        ];
    }

}
