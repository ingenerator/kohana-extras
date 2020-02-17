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
    protected function doHandle(\Throwable $e)
    {
        $this->log->add(
            \Log::WARNING,
            'DB connection error: '.\Kohana_Exception::text($e)
        );

        return $this->respondGenericErrorPage(static::PAGE_GENERIC_MAINTENANCE, 502);
    }

}
