<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SwiftMailerFactory extends OptionalDependencyFactory
{

    /**
     * @return array
     */
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
                        'class'       => static::class,
                        'constructor' => 'buildSmtpTransport',
                        'arguments'   => ['@email.relay@'],
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array|null                  $relay
     * @param \Swift_Events_EventListener ...$plugins
     *
     * @return \Swift_SmtpTransport
     */
    public static function buildSmtpTransport(
        array $relay = NULL,
        \Swift_Events_EventListener ...$plugins
    ): \Swift_SmtpTransport {

        $config = \array_merge(
            [
                'host'     => 'localhost',
                'port'     => 25,
                'security' => NULL,
                'username' => NULL,
                'password' => NULL,
            ],
            $relay ?: []
        );

        $transport = new \Swift_SmtpTransport($config['host'], $config['port'], $config['security']);
        $transport->setUsername($config['username']);
        $transport->setPassword($config['password']);

        foreach ($plugins as $plugin) {
            $transport->registerPlugin($plugin);
        }

        return $transport;
    }
}
