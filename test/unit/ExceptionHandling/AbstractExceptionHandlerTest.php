<?php


namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\Logger\SpyingLoggerStub;
use PHPUnit\Framework\TestCase;

abstract class AbstractExceptionHandlerTest extends TestCase
{
    /**
     * @var SpyingLoggerStub
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
        $this->assertContains('offline for maintenance', $response->body());
    }

    /**
     * @param \Response $response
     */
    protected function assertResponseIsGeneric500(\Response $response): void
    {
        $this->assertReturnsResponseStatus(500, $response);
        $this->assertSame('text/html;charset=utf8', $response->headers('Content-Type'));
        $this->assertContains('error has been logged', $response->body());
    }


    /**
     * @param \Throwable $actual_exception
     * @param \Throwable ...$previous_exceptions
     */
    protected function assertLoggedExceptionChain(
        \Throwable $actual_exception,
        \Throwable ...$previous_exceptions
    ) {
        $expect_msg = \Kohana_Exception::text($actual_exception);
        foreach ($previous_exceptions as $exception) {
            $expect_msg .= "\nCause: ".\Kohana_Exception::text($exception);
        }
        $this->log->assertOneLog(
            \Log::EMERGENCY,
            $expect_msg,
            NULL,
            ['exception' => $actual_exception]
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->log = new SpyingLoggerStub;
    }

}
