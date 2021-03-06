<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\Message\KohanaMessageProvider;
use Ingenerator\PHPUtils\Logging\StackdriverApplicationLogger;

class KohanaCoreFactory
{
    /**
     * @return array
     */
    public static function definitions()
    {
        return [
            'kohana' => [
                'log'              => [
                    '_settings' => [
                        'class'       => KohanaCoreFactory::class,
                        'constructor' => 'getLog',
                        'shared'      => TRUE,
                    ],
                ],
                'message_provider' => [
                    '_settings' => [
                        'class'     => KohanaMessageProvider::class,
                        'arguments' => ['%kohana.psr_log%'],
                    ],
                ],
                'psr_log'          => [
                    '_settings' => [
                        'class'       => StackdriverApplicationLogger::class,
                        'constructor' => 'instance',
                        'shared'      => TRUE,
                    ],
                ],
                'request'          => [
                    '_settings' => [
                        'class'       => KohanaCoreFactory::class,
                        'constructor' => 'getRequest',
                        'shared'      => TRUE,
                    ],
                ],
                'routes'           => [
                    '_settings' => [
                        'class'       => \Route::class,
                        'constructor' => 'all',
                        'shared'      => FALSE,
                    ],
                ],
                'session'          => [
                    '_settings' => [
                        'class'       => \Session::class,
                        'constructor' => 'instance',
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \Log
     */
    public static function getLog()
    {
        return \Kohana::$log;
    }

    /**
     * @return \Request
     */
    public static function getRequest()
    {
        return \Request::initial();
    }

}
