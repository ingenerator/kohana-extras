<?php

namespace Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\Routing\HttpMethodRouteReverseRouter;

class UrlReverseRouterFactory
{
    /**
     * @return array
     */
    public static function definitions()
    {
        return [
            'util' => [
                'url_reverse_router' => [
                    '_settings' => [
                        'class'     => HttpMethodRouteReverseRouter::class,
                        'arguments' => [
                            '%kohana.routes%',
                        ],
                        'shared'    => TRUE,
                    ],
                ],
            ],
        ];
    }
}
