<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\DependencyFactory;

use Ingenerator\KohanaExtras\DependencyFactory\SwiftMailerFactory;
use Ingenerator\PHPUtils\Monitoring\OperationTimer;
use Ingenerator\SwiftMailer\SES\Transport\SESTransport;
use Psr\Log\NullLogger;


class SwiftMailerFactoryTest extends AbstractDependencyFactoryTest
{
    protected array $stub_services = [
        'metrics.timer' => OperationTimerStub::class,
        'kohana.psr_log' => NullLogger::class,
    ];

    protected array $stub_config = [
        'email' => [
            'ses_client_options' => [],
        ],
    ];

    public function test_it_defines_swiftmailer_mailer()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_Mailer::class,
                    $this->assertDefinesService('swiftmailer.mailer', SwiftMailerFactory::definitions())
                );
            }
        );
    }

    public function test_it_defines_swiftmailer_mailer_smtp()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_Mailer::class,
                    $this->assertDefinesService('swiftmailer.mailer', SwiftMailerFactory::definitionsSMTP())
                );
            }
        );
    }

    public function test_it_defines_swiftmailer_mailer_ses()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_Mailer::class,
                    $this->assertDefinesService('swiftmailer.mailer', SwiftMailerFactory::definitionsSES())
                );
            }
        );
    }

    public function test_it_defines_swiftmailer_smtp_transport()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_SmtpTransport::class,
                    $this->assertDefinesService('swiftmailer.transport', SwiftMailerFactory::definitions())
                );
            }
        );
    }

    public function test_it_defines_swiftmailer_smtp_transport_smtp()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    \Swift_SmtpTransport::class,
                    $this->assertDefinesService('swiftmailer.transport', SwiftMailerFactory::definitionsSMTP())
                );
            }
        );
    }

    public function test_it_defines_swiftmailer_ses_transport()
    {
        $this->assertOptionalService(
            function () {
                $this->assertInstanceOf(
                    SESTransport::class,
                    $this->assertDefinesService('swiftmailer.transport', SwiftMailerFactory::definitionsSES())
                );
            }
        );
    }
}

class OperationTimerStub extends OperationTimer
{
    public function __construct()
    {
    }
}
