<?php
/**
 * @author    Craig Gosman <craig@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\DependencyFactory;

use AsyncAws\Core\Configuration;
use AsyncAws\Ses\SesClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesHttpAsyncAwsTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

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

    public static function definitionsSES(array $subscribers = []): array
    {
        static::requireClass(Mailer::class, 'symfony/mailer');
        static::requireClass(SesHttpAsyncAwsTransport::class, 'symfony/amazon-mailer');

        return [
            'symfonymailer' => [
                'mailer'     => [
                    '_settings' => [
                        'class'     => Mailer::class,
                        'arguments' => [
                            '%symfonymailer.transport%',
                        ],
                        'shared'    => TRUE,
                    ],
                ],
                'ses_client' => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildSESClient',
                        'arguments'   => [
                            '@!email.ses_client_options!@',
                            '%kohana.psr_log%',
                        ],
                        'shared'      => TRUE,
                    ],
                ],
                'transport'  => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildSESTransport',
                        'arguments'   => [
                            '%symfonymailer.ses_client%',
                            '%kohana.psr_log%',
                            ...$subscribers,
                        ],
                        'shared'      => TRUE,
                    ],
                ],
            ],
        ];
    }

    public static function buildSESClient(array $client_options, LoggerInterface $logger): SesClient
    {
        return new SesClient(
            configuration: Configuration::create(
                array_merge(
                    [
                        'region' => 'eu-west-1',
                    ],
                    $client_options
                )
            ),
            logger: $logger
        );
    }

    public static function buildSESTransport(
        SesClient                $ses_client,
        LoggerInterface          $logger,
        EventSubscriberInterface ...$subscribers
    ): SesHttpAsyncAwsTransport
    {
        return new SesHttpAsyncAwsTransport(
            sesClient: $ses_client,
            dispatcher: static::buildEventDispatcher(...$subscribers),
            logger: $logger
        );
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

        $transport = new EsmtpTransport(
            host: $config['host'],
            port: $config['port'],
            tls: $config['tls'],
            dispatcher: static::buildEventDispatcher(...$subscribers)
        );

        if ($config['username']) {
            $transport->setUsername($config['username']);
        }
        if ($config['password']) {
            $transport->setPassword($config['password']);
        }

        return $transport;
    }

    protected static function buildEventDispatcher(EventSubscriberInterface ...$subscribers): EventDispatcher
    {
        $dispatcher = new EventDispatcher();
        foreach ($subscribers as $subscriber) {
            $dispatcher->addSubscriber($subscriber);
        }

        return $dispatcher;
    }
}
