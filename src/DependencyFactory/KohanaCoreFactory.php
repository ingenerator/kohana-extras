<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

class KohanaCoreFactory
{
    /**
     * @return array
     */
    public static function definitions()
    {
        return [
            'kohana' => [
                'log'     => [
                    '_settings' => [
                        'class'       => KohanaCoreFactory::class,
                        'constructor' => 'getLog',
                        'shared'      => TRUE,
                    ],
                ],
                'request' => [
                    '_settings' => [
                        'class'       => KohanaCoreFactory::class,
                        'constructor' => 'getRequest',
                        'shared'      => TRUE,
                    ],
                ],
                'session' => [
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
