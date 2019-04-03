### Unreleased

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
