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

class SwiftMailerFactory extends OptionalDependencyFactory
{

    public static function definitions()
    {
        static::requireClass(\Swift_Mailer::class, 'swiftmailer/swiftmailer');

        return [
            'swiftmailer' => [
                'mailer'    => [
                    '_settings' => [
                        'class'       => \Swift_Mailer::class,
                        'constructor' => 'newInstance',
                        'arguments'   => ['%swiftmailer.transport%'],
                        'shared'      => TRUE,
                    ],
                ],
                'transport' => [
                    '_settings' => [
                        'class'       => \Swift_SendmailTransport::class,
                        'constructor' => 'newInstance',
                        'arguments'   => [],
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }
}
