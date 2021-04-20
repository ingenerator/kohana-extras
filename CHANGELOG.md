
### Unreleased

## v2.2.1 (2021-04-20)

* Support PHP8.0 

## v2.2.0 (2021-03-20)

* Add SwiftMailer SES transport factory definitions
* Add Metrics Agent factory definitions


## v2.1.0 (2021-03-08)

* Require that the config values for session hash salt, garbage collect token and tokenista secret
  are defined (not null) before the dependency container creates the related service.
* Add extensible HTTP-triggered garbage collection (see README)
* Add a dependency factory for our mysql session handler

## v2.0.2 (2020-11-02)
* Support SwiftMailer ^6

## v2.0.1 (2020-11-02)
* Bump kohana-dependencies to php 7.4 tested release

## v2.0.0 (2020-11-02)
* Stable release supporting php 7.4

## v2.0.0-beta5 (2020-10-30)
* Support php7.4

## v2.0.0-beta4 (2020-10-05)
* Copy the debugbar fonts (font-awesome) on first render to htdocs. Only copy the fonts if necessary. Use a separate 'dev-assets' dir under htdocs to prevent any accidental pollution / commits between this and production code.

## v2.0.0-beta3 (2020-10-05)

* Allow SwiftMailer plugins to be registered from dependencies() method

## v2.0.0-beta2 (2020-09-24)

* SwiftMailerFactory supports transport plugins being added by overriding the config

## v2.0.0-beta1 (2020-05-14)

* Default kohana.psr_log implementation now uses StackdriverApplicationLogger (which must first have been
  initialised globally).
* RequestExceptionDispatcher fallback logging implementation changed - logs JSON to STDERR by default and 
  attempts to use a StackdriverApplicationLogger instance if it exists. 
* Exception handler interface changed - implementation should now happen directly in the `handle`
  method which has a hard typehint on `Throwable $e` as the parameter and `?:Response` as the return type
  - the old `abstract protected function doHandle()` is no longer used.
* All Exception handlers now take PSR/Log and this is no longer an optional dependency. Custom exception
  tracing and previous exception chain logging is now delegated to the main logger implementation.
* KohanaMessageProvider now takes PSR/Log
* Deprecate use of Kohana_Log as a dependency to application code, in future apps should log direct 
  to a PSR/Log implementation
* Add KohanaPsrLogWriter as bridge for Kohana::$log calls to PSR\Log
* Update DependencyContainer to use the php-utils InitialisableSingletonTrait instead of own 
  implementation. Requires apps to change `DependencyContainer::initialise` to `::initialiseFromFile`

## v1.2.0 (2020-02-18)

* Register Session_Exception and ConnectionException handlers by default in the ExceptionHandlerFactory,
  can be replaced or overridden in project dependency config
* Add handler to show generic fatal error page on any Session_Exception
* Log any previous exceptions in the chain when logging an unexpected exception in the
  default handler.
* Add generic exception handler to show a friendly maintenance page with a 502 and
  reduce log verbosity on a `ConnectionException` from the DBAL package, thrown if 
  the database server is not available or not connecting properly.
* Update ExceptionHandler, AbstractExceptionHandler etc to strong \Throwable
  typehints. This is not breaking under LSP as  a child class can still define
  a method with no hard typehint (since `anything` is more generic than `\Throwable`).

## v1.1.0 (2020-02-12)

* Add UrlReverseRouter with implementation to get URLs from defined HttpMethodRoutes, and mock
  to generate them predictably from the parameters independent of any application routing rules.

## v1.0.1 (2019-04-03)

* Bump missed dependencies

## v1.0.0 (2019-04-03)

* Run php-cs-fixer native_function_invocation fix
* Drop support for php < 7.2

## v0.4.1 (2019-03-11)

* Drop support for all PHP5 
* [PHP7-ONLY] Add explicit return types to ValidationConstraint classes to match the method
  signatures in phpunit-7 and avoid fatal errors 
* Fix all risky phpunit tests to make sensible assertions

## v0.4.0 (2018-12-06)

* [BREAKING] Drop the DoctrineFactory dependency factory in favour of using the tooling 
  from ingenerator/kohana-doctrine2

## v0.3.5 (2018-09-20)

* Add KohanaPSRLogger to provide a standard PSR/Log interface to the Kohana log - 
  use it from dependencies as %kohana.psr_log%

## v0.3.4 (2018-09-06)

* Define class_alias for PHPUnit_Framework_Constraint -> namespaced version in 
  BaseValidationConstraint - otherwise the namespaced parent is a breaking change in 
  dependent projects which need to define it in their bootstrap.

## v0.3.3 (2018-09-06)

* Make project-level email relay options genuinely optional and just use defaults if
  no configuration has been set.  

## v0.3.2 (2018-09-04)

* Fix missing ingenerator/php-utils dependency
* Fix missed Constraint namespaced PHPUnit class

## v0.3.1 (2018-08-04)

* Use SMTP transport as default for SwiftMailer
* Use namespaced PHPUnit classes to support PHPUnit 7

## v0.3.0 (2018-03-13)

* Add RequestExceptionDispatcher and default handler for dealing with exceptions / errors during execution
  and logging / sending friendly responses.
* Add HttpMethodRoute::createExplicit shorthand for routes that don't take a controller param
* Support class names ending Controller for HttpMethodRoute controllers (e.g. Any\Namespace\DoThingsController)
* Add HttpMethodRoute for mapping requests to a single controller per URL with the action varying
  by request method
* Add RequestExecutorFactory for defining the request executor to use
* Add service definition for \Route::all in KohanaCoreFactory
* Add new ContainerAwareRequestExecutor with support for loading controllers defined
  in the DI container.
* Refactor DependencyContainer to support loading from an array again as well as from 
  a config file (e.g. for use in unit tests etc).
* Add `->has` support to DependencyContainer for checking if a service exists
* Now requires the inGenerator fork of Kohana

## v0.2.3 (2018-03-07)

* Add service config options to the Doctrine dependency factory to enable adding event subscribers

## v0.2.2 (2018-02-26)

* Add config options to the tokenista dependency factory 

## v0.2.1 (2018-02-22)

* Extract ImmutableKohanaValidation - simple wrapper for Kohana validation that
  splits the interfaces for performing validation and getting the result, and makes
  running the validation a one-time process that prevents subsequent changes to data,
  values, errors, etc.
* Add common helpers / factory for maximebf/debugbar integration

## v0.2.0 (2018-02-20)

* Update to use ingenerator/kohana-dependencies at 0.9 version

## v0.1.1 (2018-02-15)

* Added customised DependencyContainer with explicit definition of included module configs.
* Added basic dependency providers for Kohana core and a selection of commonly-used third 
  party libraries (which need to be separately included if required). 

### v0.1.0 (2018-02-13)

* First version, extracted from host project
