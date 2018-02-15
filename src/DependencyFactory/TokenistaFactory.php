<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\Tokenista;

class TokenistaFactory extends OptionalDependencyFactory
{

    public static function definitions()
    {
        static::requireClass(Tokenista::class, 'ingenerator/tokenista');

        return [
            'tokenista' => [
                'tokenista' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'makeTokenista',
                        'arguments'   => [
                            '@tokenista.secret@',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function makeTokenista($secret)
    {
        if ( ! $secret) {
            throw new \InvalidArgumentException('No tokenista.secret has been configured');
        }

        return new Tokenista($secret);
    }
}
