<?php
/**
 * Implements a covariant method call to enable a
 * primitive type of method overloading.
 *
 * This class allows the creation of a dispatch table
 * based on the concrete sub-type of _one_ parameter.
 *
 * Since it's not possible to have two methods like:
 *
 * public function purchase(Motorbike $motorbike)
 * public function purchase(Car $car)
 *
 * using this macro will allow you to reduce the
 * boilerplate code required to handle such
 * scenario.
 *
 * public function purchase(Vehicle $vehicle) {
 *    return covariant($vehicle);
 * }
 *
 * and overload your methods in the following way:
 *
 * public function purchaseCar(Car $car)
 * public function purchaseMotorbike(Motorbike $bike)
 */

/**
 * Declare a covariant method.
 *
 * @param  object    $subject
 * @param  object    $parameter
 * @param  sequence  $handlerSequence
 */
function covariant($subject, $parameter, ...$handlerSequence)
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
