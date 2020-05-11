<?php


namespace Ingenerator\KohanaExtras\ExceptionHandling;


/**
 * Handles Session_Exception, thrown on any error while trying to read / start the session
 *
 * Just serves a generic error page and logs it : there is nothing useful the user can do to handle
 * it and the exception messages and stack traces are supremely unhelpful to show to the user
 * anyway.
 */
class SessionExceptionHandler extends AbstractExceptionHandler
{
    public function handle(\Throwable $e): ?\Response
    {
        $this->logException($e);

        return $this->respondGenericErrorPage(static::PAGE_GENERIC_ERROR, 500);
    }

}
