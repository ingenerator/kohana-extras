<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace Ingenerator\KohanaExtras\ExceptionHandling;


/**
 * Handles one or more types of exceptions, including returning a suitable response to the end user.
 * This might mean sending a 404 page or an error page, redirecting them to login, or otherwise
 * dealing with the generic application-level exception in a helpful way.
 *
 * Handlers should be registered with the RequestExceptionDispatcher and will be tried in turn based
 * on the exeption class they've registered for, until one returns a response.
 *
 * If you want to handle some instances of a particular class but not others then register for the class
 * and in your handler return either a response or null.
 *
 * If you register for \Exception, you will only get \Exception : you won't get \Throwable on a PHP7
 * platform.
 *
 * Do not assume Kohana has been initialised unless you *know* the exception you're registered for can
 * only be thrown later in execution. For example, if you register on \Exception there's no guarantee
 * any part of Kohana is actually there.
 *
 * If a handler throws (or causes) a further exception then all handling stops, the dispatcher will log
 * an emergency to syslog and return a completely generic 500 page to the end-user. This should not be
 * expected production behaviour.
 *
 * By handling an exception, you also take responsibility for logging it if required. If you don't log it,
 * but you return a response, it won't be logged.
 *
 * @package Ingenerator\KohanaExtras\ExceptionHandling
 */
interface ExceptionHandler
{

    /**
     * Note that handlers can define their expected type in phpdoc but cannot define a hard hint
     * in the method signature as this would violate the Liskov Substitution Principle
     *
     * @param \Throwable $e
     *
     * @return \Response|null
     */
    public function handle(\Throwable $e): ?\Response;

}
