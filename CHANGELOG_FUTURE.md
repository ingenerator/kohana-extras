This file documents future planned breaking changes.

* Move ExceptionHandler implementations from doHandle() to handle() now that
  we don't need to manually check for \Exception|\Throwable in the parent
* Add hard typehint to the exception handler methods (`:?Response`) - this is
  breaking on existing children.
