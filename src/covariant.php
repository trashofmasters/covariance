<?php
/**
 * Declare a covariant method.
 *
 * @param  object    $subject
 * @param  object    $parameter
 */
function covariant($subject, $parameter)
{
    $e = new BadFunctionCallException(
        "Only class methods can be declared as covariant."
    );

    $trace = $e->getTrace();
    if ('->' !== $trace[1]['type']) {
        throw $e;
    }

    $method = $trace[1]['function'];
    return (new Ofc\Covariance\CovariantCall($method))
        ->setSubjectType($subject)
        ->execute($parameter);
}
