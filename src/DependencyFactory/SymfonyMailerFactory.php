<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use function array_map;
use function array_merge;

class SymfonyMailerFactory extends OptionalDependencyFactory
{

    public static function definitionsSMTP(array $subscribers = []): array
    {
        static::requireClass(Mailer::class, 'symfony/mailer');

        return [
            'symfonymailer' => [
                'mailer'    => [
                    '_settings' => [
                        'class'     => Mailer::class,
                        'arguments' => ['%symfonymailer.transport%'],
                        'shared'    => TRUE,
                    ],
                ],
                'transport' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildSmtpTransport',
                        'arguments'   => ['@!email.relay!@', ...$subscribers],
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }

    public static function buildSmtpTransport(
        array                    $relay = [],
        EventSubscriberInterface ...$subscribers
    ): EsmtpTransport
    {
        $config = array_merge(
            [
                'host'     => 'localhost',
                'port'     => 25,
                'tls'      => FALSE,
                'username' => NULL,
                'password' => NULL,
            ],
            $relay
        );

        $dispatcher = new EventDispatcher();
        array_map(
            fn(EventSubscriberInterface $subscriber) => $dispatcher->addSubscriber($subscriber),
            $subscribers
        );

        $transport = new EsmtpTransport(
            host: $config['host'],
            port: $config['port'],
            tls: $config['tls'],
            dispatcher: $dispatcher
        );

        if ($config['username']) {
            $transport->setUsername($config['username']);
        }
        if ($config['password']) {
            $transport->setPassword($config['password']);
        }

        return $transport;
    }
}
