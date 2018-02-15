<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


abstract class OptionalDependencyFactory
{
    /**
     * @param string $class
     * @param string $package
     */
    protected static function requireClass($class, $suggest_package)
    {
        if ( ! class_exists($class)) {
            throw MissingOptionalDependencyException::missingClass($class, $suggest_package);
        }
    }

}
