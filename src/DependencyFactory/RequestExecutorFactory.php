<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\RequestExecutor\ContainerAwareRequestExecutor;

class RequestExecutorFactory
{

    public static function definitions()
    {
        return [
            'kohana' => [
                'request_executor' => [
                    '_settings' => [
                        'class'     => ContainerAwareRequestExecutor::class,
                        'arguments' => [
                            '%kohana.routes%',
                            '%dependencies%'
                        ]
                    ]
                ]
            ]
        ];
    }
}
