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
        // Note that DBAL ConnectionException is thrown for a variety of error conditions including some that are
        // more properly runtime errors e.g. incorrect use of transactions / rollback when no transaction active etc.
        // So although we show the maintenance page for everything, we log these as a full exception for error reporting
        // and review.
        $this->log->warning(
            'DB connection error: '.\Kohana_Exception::text($e),
            ['exception' => $e]
        );

        return $this->respondGenericErrorPage(static::PAGE_GENERIC_MAINTENANCE, 503);
    }

}
