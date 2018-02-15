<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyContainer;


class DependencyConfigParser
{

    /**
     * Merge `_include` blocks into the main configuration array, overriding each occurrence with later ones
     *
     * @param array $base_config
     *
     * @return array
     */
    public function parse(array $base_config)
    {
        $merged_config = [];
        foreach (\Arr::get($base_config, '_include', []) as $include) {
            $merged_config = \Arr::merge($merged_config, $include);
        }

        $merged_config = \Arr::merge($merged_config, $base_config);

        unset($merged_config['_include']);

        return $merged_config;
    }
}
