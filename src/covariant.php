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

    $parameter = array_shift($arguments);
    return (new Ofc\Covariance\CovariantCall($method))
        ->setCallSubject($subject)
        // ->setRemainingArgs($arguments)
        ->execute($parameter);
}
