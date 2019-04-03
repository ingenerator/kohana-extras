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

    /**
     * Helper to generate dependency definitions for a simple list of controller class => dependencies
     *
     * Produces the definitions with the keys / in the format the request executor expects to find
     * them.
     *
     * eg:
     *   _include => [
     *     RequestExecutorFactory::controllerDefinitions(
     *       [
     *         Some\Custom\Controller::class => ['%kohana.log%', '@some.config@'],
     *       ]
     *     )
     *   ]
     *
     * @param array $controllers
     *
     * @return array
     */
    public static function controllerDefinitions(array $controllers)
    {
        $definitions = ['controller' => []];
        foreach ($controllers as $controller_class => $controller_args) {
            $key                             = '\\'.\trim($controller_class, '\\');
            $definitions['controller'][$key] = [
                '_settings' => [
                    'class'     => $controller_class,
                    'arguments' => $controller_args,
                ],
            ];
        }

        return $definitions;
    }
}
