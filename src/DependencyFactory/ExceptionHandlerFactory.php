<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use Ingenerator\KohanaExtras\ExceptionHandling\DefaultRequestExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;

class ExceptionHandlerFactory
{

    /**
     * Create definitions for the default request exception handling framework.
     *
     * Optionally pass in an array of custom handlers you want to register : these will be
     * processed in strict order and the first one that matches an exception class and returns
     * a response will win.
     *
     * For example to add a handler for any Entity\NotFoundException:
     *
     *    ExceptionHandlerFactory::definitions([
     *      [
     *        // This is the exception type this handler should match
     *        'type'    => \Entity\NotFoundException::class,
     *        'handler' => [
     *          // This is just a standard _settings definition for the handler as a service
     *          // We will define it as exception_handler.handler.My\NotFoundExceptionHandler
     *          // And pass the appropriate mapping and service reference to the exception dispatcher's constructor
     *          'class'     => My\NotFoundExceptionHandler::class,
     *          'arguments' => ['%view.error.404%', '%view.renderer%']
     *        ],
     *      ]
     *    ]);
     *
     * @param array $handlers
     *
     * @return array
     */
    public static function definitions(array $handlers = [])
    {
        $base = [
            'exception_handler' => [
                'default'    => [
                    '_settings' => [
                        'class'     => DefaultRequestExceptionHandler::class,
                        'arguments' => ['%kohana.log%'],
                    ],
                ],
                'dispatcher' => [
                    '_settings' => [
                        'class'     => RequestExceptionDispatcher::class,
                        'arguments' => [
                            '%exception_handler.default%',
                            '%dependencies%',
                            [],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($handlers as $handler_def) {
            $class = $handler_def['handler']['class'];

            // Define it as a service in its own right
            $base['exception_handler']['handler'][$class]['_settings'] = $handler_def['handler'];

            // Define the reference in the dispatcher config mapping
            $base['exception_handler']['dispatcher']['_settings']['arguments'][2][] = [
                'type'    => $handler_def['type'],
                'handler' => 'exception_handler.handler.'.$class,
            ];
        }

        return $base;
    }
}
