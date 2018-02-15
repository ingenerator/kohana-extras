<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Driver\PDOConnection;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

class SymfonyValidationFactory extends OptionalDependencyFactory
{

    public static function definitions()
    {
        static::requireClass(AnnotationRegistry::class, 'doctrine/annotations');
        static::requireClass(RecursiveValidator::class, 'symfony/validation');

        return [
            'validation' => [
                'validator' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildValidator',
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    public static function buildValidator()
    {
        // The Doctrine annotation loader does not by default autoload because some PSR-0 autoloaders are badly behaved
        // and emit warnings/output/errors when a class can't be found. Ours don't so it's safe to just use class_exists
        // as a global autoloader.
        AnnotationRegistry::registerLoader(function ($class) { return class_exists($class); });
        $builder = Validation::createValidatorBuilder();
        $builder->enableAnnotationMapping();

        // @todo: need to enable metadata cache before using the validator from a web context

        return $builder->getValidator();
    }

}
