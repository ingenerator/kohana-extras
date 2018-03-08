### Unreleased

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
