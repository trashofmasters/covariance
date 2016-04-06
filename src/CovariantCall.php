<?php

namespace Ofc\Covariance;

use Closure;
use BadMethodCallException;

class CovariantCall
{
    /**
     * Name of the method being augmented with covariant behaviour.
     *
     * @type string
     */
    private $covariantName;

    /**
     * Optional body for the covariant method.
     *
     * @type callbable
     */
    private $covariantBody;

    /**
     * Name of the class on which the covariant method
     * is actually called. This is relevant for polymorphic
     * calls.
     *
     * @type string
     */
    private $callSubject;

    public function __construct($covariantName)
    {
        $this->covariantName = $covariantName;
    }

    public function addHandler($possibleType, $handler)
    {
        $this->dispatchTable[$this->className($possibleType)] = $handler;
        return $this;
    }

    public function getHandlerForType($possibleType)
    {
        $className = $this->className($possibleType);
        if (isset($this->dispatchTable[$className])) {
            return $this->dispatchTable[$className];
        }
    }

    public function setCallSubject($possibleType)
    {
        $this->callSubject = $possibleType;
        return $this;
    }

    public function setMethodBody($covariantBody)
    {
        $this->covariantBody = $covariantBody;
        return $this;
    }

    public function execute($parameter, ...$additionalArgs)
    {
        $handler = $this->getHandlerForType($parameter);
        $subject = $this->callSubject;
        $methodName = $this->covariantName;
        $methodBody = $this->covariantBody;

        // TODO get rid of this flag.
        $extraArg = false;
        if (empty($handler)) {
            $handler = $this->findHandlerLineage(
                $subject,
                $methodName,
                $parameter
            );
            $extraArg = true;
            array_unshift($additionalArgs, $parameter);
        }

        if ($methodBody and empty($handler)) {
            $handler = $methodBody;
        }

        if (empty($handler)) {
            $targetClassName = $this->className($parameter);
            $subjectClassName = $this->className($subject);

            $message = sprintf(
                "Cannot pass object of instance %s to covariant method %s::%s.",
                $targetClassName,
                $subjectClassName,
                $methodName
            );

            throw new BadMethodCallException($message);
            // NOTE: this isn't too bad, but perhaps only useful
            // when the program is statically checked.
            // trigger_error($message, E_USER_ERROR);
            // exit;
        }

        if ($handler instanceof Closure) {
            return $handler($parameter, ...$additionalArgs);
        }

        return call_user_func_array(
            $handler,
            $additionalArgs
            // array_slice($additionalArgs, (int) $extraArg)
        );
    }

    public function findHandlerLineage($callSubject, $covariantName, $parameter) {
        $subjectClassName = $this->className($callSubject);
        $parameterClassName = $this->className($parameter);

        $classes = array_values(class_parents($subjectClassName));
        array_unshift($classes, $subjectClassName);

        foreach ($classes as $className) {
            $methodName = $this->methodName(
                $covariantName,
                $parameterClassName
            );
            if (method_exists($className, $methodName)) {
                if ($className === $subjectClassName) {
                    return [$callSubject, $methodName];
                }
            }
        }
        return false;
    }

    public function className($possibleType)
    {
        if (is_string($possibleType)) {
            return rtrim($possibleType, '\\');
        }
        return rtrim(get_class($possibleType), '\\');
    }

    public function methodName($methodName, $className)
    {
        $className = substr(
            $className,
            strrpos($className, '\\')
        );
        return $methodName . trim($className, '\\');
    }
}
