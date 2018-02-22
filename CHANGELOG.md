### Unreleased

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
