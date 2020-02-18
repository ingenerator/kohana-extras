<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\ExceptionHandling\DefaultRequestExceptionHandler;
use Ingenerator\KohanaExtras\Logger\SpyingLoggerStub;

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
        $this->log = new SpyingLoggerStub;
        $this->newSubject()->handle(\HTTP_Exception::factory(400));
        $this->log->assertNothingLogged();
    }

    public function test_it_returns_generic_error_response_from_any_other_exception()
    {
        $e        = new \InvalidArgumentException('This is broken');
        $response = $this->assertReturnsResponseStatus(500, $this->newSubject()->handle($e));
        $this->assertStringStartsWith('text/html', $response->headers('content-type'));
    }

    public function test_it_logs_generic_exceptions()
    {
        $this->log = new SpyingLoggerStub;
        $e         = new \BadMethodCallException('Ooops');
        $this->newSubject()->handle($e);
        $this->log->assertOneLog(
            \Log::EMERGENCY,
            \Kohana_Exception::text($e),
            NULL,
            ['exception' => $e]
        );
    }

    public function test_it_logs_generic_exceptions_with_their_previous_chain_if_any()
    {
        $this->log = new SpyingLoggerStub;
        $e_cause   = new \BadMethodCallException('Oooops', 20);
        $e_rethrow = new \RuntimeException('Cannot do something', 0, $e_cause);
        $e_final   = new \InvalidArgumentException('Thing is not valid', 0, $e_rethrow);
        $this->newSubject()->handle($e_final);
        $this->assertLoggedExceptionChain($e_final, $e_rethrow, $e_cause);
    }

    public function test_it_logs_to_global_kohana_log_if_nothing_injected()
    {
        $old_log = \Kohana::$log;
        try {
            \Kohana::$log = $log = new SpyingLoggerStub;
            $this->log    = NULL;
            $this->newSubject()->handle(new \BadMethodCallException('Darn'));
            $log->assertLogsMatching(['/Darn/']);
        } finally {
            \Kohana::$log = $old_log;
        }
    }

    public function test_it_throws_if_there_is_no_log_to_log_to()
    {
        $old_log = \Kohana::$log;
        try {
            \Kohana::$log       = $this->log = NULL;
            $original_exception = new \BadMethodCallException('Oh man');
            $this->newSubject()->handle($original_exception);
            $this->fail('Should have thrown');
        } catch (\RuntimeException $caught_exception) {
            $this->assertContains(
                $original_exception->getMessage(),
                $caught_exception->getMessage()
            );
            $this->assertSame($original_exception, $caught_exception->getPrevious());
        } finally {
            \Kohana::$log = $old_log;
        }
    }

    /**
     * @return DefaultRequestExceptionHandler
     */
    protected function newSubject()
    {
        return new DefaultRequestExceptionHandler($this->log);
    }

}
