<?php


namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;

abstract class AbstractExceptionHandlerTest extends TestCase
{
    /**
     * @var TestLogger
     */
    protected $log;

    /**
     * @param int       $expect_status
     * @param \Response $response
     *
     * @return \Response
     */
    protected function assertReturnsResponseStatus($expect_status, $response)
    {
        $this->assertInstanceOf(\Response::class, $response);
        $this->assertSame($expect_status, $response->status());

        return $response;
    }

    /**
     * @param \Response $response
     */
    protected function assertResponseIsMaintenance503(\Response $response): void
    {
        $this->assertReturnsResponseStatus(503, $response);
        $this->assertSame('text/html;charset=utf8', $response->headers('Content-Type'));
        $this->assertStringContainsString('offline for maintenance', $response->body());
    }

    /**
     * @param \Response $response
     */
    protected function assertResponseIsGeneric500(\Response $response): void
    {
        $this->assertReturnsResponseStatus(500, $response);
        $this->assertSame('text/html;charset=utf8', $response->headers('Content-Type'));
        $this->assertStringContainsString('error has been logged', $response->body());
    }


    /**
     * @param \Throwable $exception
     */
    protected function assertLoggedException(\Throwable $exception)
    {
        $expect_msg = \Kohana_Exception::text($exception);
        $this->assertCount(1, $this->log->records);
        $this->assertSame(
            [
                'level'   => LogLevel::EMERGENCY,
                'message' => $expect_msg,
                'context' => ['exception' => $exception],

            ],
            $this->log->records[0]
        );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->log = new TestLogger;
    }

}
