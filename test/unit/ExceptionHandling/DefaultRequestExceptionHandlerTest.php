<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\ExceptionHandling\DefaultRequestExceptionHandler;
use Psr\Log\Test\TestLogger;

class DefaultRequestExceptionHandlerTest extends AbstractExceptionHandlerTest
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(DefaultRequestExceptionHandler::class, $this->newSubject());
    }

    public function test_it_returns_http_response_from_http_exception()
    {
        $e = \HTTP_Exception::factory(302);
        /** @var \HTTP_Exception_302 $e */
        $e->location('http://some/page');

        $response = $this->assertReturnsResponseStatus(302, $this->newSubject()->handle($e));
        $this->assertSame('http://some/page', $response->headers('location'));
    }

    public function test_it_does_not_log_http_exception()
    {
        $this->log = new TestLogger;
        $this->newSubject()->handle(\HTTP_Exception::factory(400));
        $this->assertSame([], $this->log->records);
    }

    public function test_it_returns_generic_error_response_from_any_other_exception()
    {
        $e        = new \InvalidArgumentException('This is broken');
        $response = $this->assertReturnsResponseStatus(500, $this->newSubject()->handle($e));
        $this->assertStringStartsWith('text/html', $response->headers('content-type'));
    }

    public function test_it_logs_generic_exceptions_with_exception_in_context()
    {
        $e = new \BadMethodCallException('Ooops');
        $this->newSubject()->handle($e);
        $this->assertLoggedException($e);
    }

    /**
     * @return DefaultRequestExceptionHandler
     */
    protected function newSubject()
    {
        return new DefaultRequestExceptionHandler($this->log);
    }

}
