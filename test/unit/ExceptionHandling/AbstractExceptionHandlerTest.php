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
    protected function assertResponseIsMaintenance502(\Response $response): void
    {
        $this->assertReturnsResponseStatus(502, $response);
        $this->assertSame('text/html;charset=utf8', $response->headers('Content-Type'));
        $this->assertContains('offline for maintenance', $response->body());
    }

    public function setUp()
    {
        parent::setUp();
        $this->log = new SpyingLoggerStub;
    }

}
