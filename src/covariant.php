<?php
/**
 * Declare a covariant method.
 *
 * The subject S is the object receiving the message, and the method
 * to be called is decided based on the type T of the first parameter
 * of the covariant method.
 *
 * When method body is provided, it will be called if no method was
 * found which could handle the type t in T.
 *
 * The search for a method is done in the by concatenating the class
 * name of the type t to the name being declared covariant.
 *
 * Therefore a method overriding this one:
 *
 * public function print(DomainObject $object) {
 *   return covariant($this, function (DomainObject $object) {
 *     // base print method behaviour.
 *   });
 * }
 *
 * could be called printCustomer, printInvoiceLineItem, etc, each
 * accepting their own parameter in t.
 *
 * @param  object   $subject
 * @param  callable $methodBody
 */
function covariant($subject, callable $methodBody = null)
{
    $e = new BadFunctionCallException(
        "Only class methods can be declared as covariant."
    );

    $trace = $e->getTrace();
    if ('->' !== $trace[1]['type']) {
        throw $e;
    }

    $method = $trace[1]['function'];
    $arguments = $trace[1]['args'];
    $parameter = array_shift($arguments);

    return (new Ofc\Covariance\CovariantCall($method))
        ->setCallSubject($subject)
        ->setMethodBody($methodBody)
        ->execute($parameter, ...$arguments);
}
