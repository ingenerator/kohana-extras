<?php


namespace test\unit\Ingenerator\KohanaExtras\ExceptionHandling;

use Doctrine\DBAL\Exception\ConnectionException;
use Ingenerator\KohanaExtras\ExceptionHandling\DBALConnectionExceptionHandler;
use Psr\Log\LogLevel;

class DBALConnectionExceptionHandlerTest extends AbstractExceptionHandlerTest
{

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(DBALConnectionExceptionHandler::class, $this->newSubject());
    }

    public function test_it_logs_warning_without_full_exception_trace()
    {
        $e = new ConnectionException('SQL whatever from doctrine');
        $this->newSubject()->handle($e);
        $this->assertSame(
            [
                [
                    'level'   => LogLevel::WARNING,
                    'message' => 'DB connection error: '.\Kohana_Exception::text($e),
                    'context' => [],
                ],
            ],
            $this->log->records
        );
    }

    public function test_it_renders_generic_maintenance_page_as_502()
    {
        $e_dbal   = new ConnectionException('SQL whatever from doctrine');
        $response = $this->newSubject()->handle($e_dbal);
        $this->assertResponseIsMaintenance503($response);
    }

    protected function newSubject()
    {
        return new DBALConnectionExceptionHandler(
            $this->log
        );
    }


}
