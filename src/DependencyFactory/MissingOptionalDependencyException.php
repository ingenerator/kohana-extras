<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


class MissingOptionalDependencyException extends \InvalidArgumentException
{

    /**
     * @param string $class
     * @param string $package
     *
     * @return static
     */
    public static function missingClass($class, $package)
    {
        return new static(
            sprintf('Could not find the `%s` class - have you installed the `%s` package?', $class, $package)
        );
    }

}
