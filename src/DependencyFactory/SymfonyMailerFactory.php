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
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Bridge\Amazon\Transport\SesHttpAsyncAwsTransport;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridApiTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;
use UnexpectedValueException;
use function array_merge;
use function str_replace;

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

    public static function definitionsSendGrid(array $subscribers = []): array
    {
        static::requireClass(Mailer::class, 'symfony/mailer');
        static::requireClass(SendgridApiTransport::class, 'symfony/sendgrid-mailer');

        return [
            'symfonymailer' => [
                'mailer'      => [
                    '_settings' => [
                        'class'     => Mailer::class,
                        'arguments' => ['%symfonymailer.transport%'],
                        'shared'    => TRUE,
                    ],
                ],
                'http_client' => [
                    '_settings' => [
                        'class'       => HttpClient::class,
                        'constructor' => 'create',
                        'shared'      => TRUE,
                    ],
                ],
                'transport'   => [
                    '_settings' => [
                        'class'       => static::class,
                        'constructor' => 'buildSendGridTransport',
                        'arguments'   => [
                            '@!email.relay!@',
                            '%symfonymailer.http_client%',
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

    public static function buildSendGridTransport(
        array                    $config,
        HttpClientInterface      $client,
        LoggerInterface          $logger,
        EventSubscriberInterface ...$subscribers
    ): SendgridApiTransport
    {
        return new SendgridApiTransport(
            key: $config['api_key'],
            client: isset($config['mock_endpoint']) ?
                self::stubSendgridMockEndpointClient($config['mock_endpoint'], $client) : $client,
            dispatcher: static::buildEventDispatcher(...$subscribers),
            logger: $logger
        );
    }

    private static function stubSendgridMockEndpointClient(
        string              $mock_endpoint,
        HttpClientInterface $client
    ): HttpClientInterface
    {
        return new class ($mock_endpoint, $client) implements HttpClientInterface {

            public function __construct(private string $mock_endpoint, private HttpClientInterface $client)
            {
            }

            public function request(string $method, string $url, array $options = []): ResponseInterface
            {
                if (str_starts_with($url, 'https://api.sendgrid.com/')) {
                    return $this->client->request(
                        $method,
                        str_replace('https://api.sendgrid.com', $this->mock_endpoint, $url),
                        $options
                    );
                }

                throw new UnexpectedValueException('Expecting request to "https://api.sendgrid.com..." not "' . $url . '"');
            }

            public function stream(
                iterable|ResponseInterface $responses,
                float                      $timeout = NULL
            ): ResponseStreamInterface
            {
                return $this->client->stream($responses, $timeout);
            }

            public function withOptions(array $options): static
            {
                $clone = clone($this);
                $clone->client = $clone->client->withOptions($options);

                return $clone;
            }
        };
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
