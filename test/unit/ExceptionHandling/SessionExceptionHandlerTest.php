<?php


namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;


use Ingenerator\KohanaExtras\ExceptionHandling\SessionExceptionHandler;

class SessionExceptionHandlerTest extends AbstractExceptionHandlerTest
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(SessionExceptionHandler::class, $this->newSubject());
    }

    public function test_it_logs_emergency_with_exception_chain()
    {
        $e_cause   = new \InvalidArgumentException('There was a problem');
        $e_rethrow = new \RuntimeException('Anything at all in the tree', 0, $e_cause);
        $e_final   = new \Session_Exception('Could not read session', [], 0, $e_rethrow);

        $this->newSubject()->handle($e_final);
        $this->assertLoggedExceptionChain($e_final, $e_rethrow, $e_cause);
    }

    public function test_it_renders_generic_error_page()
    {
        $e3       = new \Session_Exception(
            'The session did not work', [], 0,
            new \RuntimeException('Thing is broken')
        );
        $response = $this->newSubject()->handle($e3);
        $this->assertResponseIsGeneric500($response);
    }

    protected function newSubject()
    {
        return new SessionExceptionHandler(
            $this->log
        );
    }

}

