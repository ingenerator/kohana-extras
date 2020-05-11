<?php


namespace Ingenerator\KohanaExtras\ExceptionHandling;


class DBALConnectionExceptionHandler extends AbstractExceptionHandler
{

    /**
     * Handle when the database is not reachable, usually due to e.g. a maintenance window
     *
     * @param \Doctrine\DBAL\Exception\ConnectionException $e
     *
     * @return \Response|null
     */
    public function handle(\Throwable $e): ?\Response
    {
        $this->log->warning(
            'DB connection error: '.\Kohana_Exception::text($e)
        );

        return $this->respondGenericErrorPage(static::PAGE_GENERIC_MAINTENANCE, 503);
    }

}
