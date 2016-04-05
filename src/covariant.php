<?php
/**
 * Declare a covariant method.
 *
 * @param  object    $subject
 */
function covariant($subject)
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

    $parameter = $arguments[0];
    return (new Ofc\Covariance\CovariantCall($method))
        ->setSubjectType($subject)
        ->execute($parameter);
}
