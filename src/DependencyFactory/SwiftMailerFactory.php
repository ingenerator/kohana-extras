<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;


use function array_merge;

class SwiftMailerFactory extends OptionalDependencyFactory
{

    /**
     * @param array $config
     *
     * @return array
     */
    public static function definitions($config = ['plugins' => []])
    {
        static::requireClass(\Swift_Mailer::class, 'swiftmailer/swiftmailer');

        return [
            'swiftmailer' => [
                'mailer'    => [
                    '_settings' => [
                        'class'       => \Swift_Mailer::class,
                        'arguments'   => ['%swiftmailer.transport%'],
                        'shared'      => TRUE,
                    ],
                ],
                'transport' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildSmtpTransport',
                        'arguments'   => array_merge(['@email.relay@'], $config['plugins'] ?? []),
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
