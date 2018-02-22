<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use DebugBar\StandardDebugBar;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Driver\PDOConnection;
use Ingenerator\KohanaExtras\DebugBar\DebugBar;

class DebugBarFactory extends OptionalDependencyFactory
{

    public static function definitions()
    {
        static::requireClass(StandardDebugBar::class, 'maximebf/debugbar');

        return [
            'debug_bar' => [
                'bar' => [
                    '_settings' => [
                        'class'     => DebugBar::class,
                        'arguments' => [
                            '%dependencies%',
                        ],
                        'shared'    => TRUE,
                    ],
                ],
            ],
        ];
    }

}
