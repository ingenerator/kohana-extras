<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\ExceptionHandling\DBALConnectionExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\DefaultRequestExceptionHandler;
use Ingenerator\KohanaExtras\ExceptionHandling\RequestExceptionDispatcher;
use Ingenerator\KohanaExtras\ExceptionHandling\SessionExceptionHandler;

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
     * The package already provides default handlers for:
     *   * Session_Exception (shows a generic error)
     *   * \Doctrine\DBAL\Exception\ConnectionException (shows a 503 maintenance page).
     *
     * These are processed last in the handler stack. You can replace, disable, or customise the
     * order of the default handlers by defining a handler on the same exception class in your own
     * list of handler definitions. To disable the handler, pass a `'handler' => FALSE`.
     *
     * For example to disable the Session_Exception handler:
     *
     *    ExceptionHandlerFactory::definitions([
     *      [
     *        'type'    => \Session_Exception::class,
     *        'handler' => FALSE
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
                        'arguments' => ['%kohana.psr_log%'],
                    ],
                ],
                'dispatcher' => [
                    '_settings' => [
                        'class'     => RequestExceptionDispatcher::class,
                        'arguments' => [
                            '%exception_handler.default%',
                            '%dependencies%',
                            '%kohana.psr_log%',
                            [],
                        ],
                    ],
                ],
            ],
        ];

        $handlers = self::appendDefaultHandlersIfNotCustomised($handlers);

        foreach ($handlers as $handler_def) {
            if ($handler_def['handler'] === FALSE) {
                // This has just been defined to disable a default custom handler
                continue;
            }

            $class = $handler_def['handler']['class'];

            // Define it as a service in its own right
            $base['exception_handler']['handler'][$class]['_settings'] = $handler_def['handler'];

            // Define the reference in the dispatcher config mapping
            $base['exception_handler']['dispatcher']['_settings']['arguments'][3][] = [
                'type'    => $handler_def['type'],
                'handler' => 'exception_handler.handler.'.$class,
            ];
        }

        return $base;
    }

    /**
     * @param array $handlers
     *
     * @return array
     */
    protected static function appendDefaultHandlersIfNotCustomised(array $handlers): array
    {
        $default_handlers = [
            [
                'type'    => \Doctrine\DBAL\Exception\ConnectionException::class,
                'handler' => [
                    'class'     => DBALConnectionExceptionHandler::class,
                    'arguments' => ['%kohana.psr_log%'],
                ],
            ],
            [
                'type'    => \Session_Exception::class,
                'handler' => [
                    'class'     => SessionExceptionHandler::class,
                    'arguments' => ['%kohana.psr_log%'],
                ],
            ],
        ];

        // Map the types that are already defined by the user's own handlers
        $explicitly_handled_types = [];
        foreach ($handlers as $handler_def) {
            $explicitly_handled_types[$handler_def['type']] = TRUE;
        }

        // Append the handlers that are not customised
        foreach ($default_handlers as $handler_def) {
            if ( ! isset($explicitly_handled_types[$handler_def['type']])) {
                $handlers[] = $handler_def;
            }
        }

        return $handlers;
    }
}
