<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\ExceptionHandling;


use Kohana_Exception;

/**
 * Replicates the historic behaviour of the Kohana request client default handling,
 * except that:
 *
 *  * It doesn't attempt to catch exceptions from generating responses, instead letting these
 *    bubble to the dispatcher
 *  * It doesn't silently skip logging exceptions if the log isn't initialised, instead
 *    it throws so that the dispatcher can log to syslog and return a generic error
 *
 * @package Ingenerator\KohanaExtras\ExceptionHandling
 */
class DefaultRequestExceptionHandler extends AbstractExceptionHandler
{

    /**
     * {@inheritdoc}
     * @see \Kohana_Exception::_handler() - this is like that, but not quite the same
     */
    public function handle(\Throwable $e): ?\Response
    {
        if ($e instanceof \HTTP_Exception) {
            return $e->get_response();
        } else {
            $this->logException($e);

            return Kohana_Exception::response($e);
        }
    }
}
