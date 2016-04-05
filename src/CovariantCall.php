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
        $this->callSubject = $this->className($possibleType);
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

        // TODO get rid of this flag.
        $oneMoreArg = false;
        if (empty($handler)) {
            $handler = $this->findHandlerLineage(
                $parameter,
                $this->covariantName,
                $this->callSubject
            );
            $oneMoreArg = true;
            array_unshift($additionalArgs, $parameter);
        }

        if (empty($handler)) {
            if ($oneMoreArg) {
                array_shift($additionalArgs);
            }
            // when the conditional branch above
            // is executed there's a duplicate parameter.
            $handler = $this->covariantBody;
        }

        if (empty($handler)) {
            $targetClass = $this->className($parameter);
            $subjectClass = $this->callSubject;
            $covariantName = $this->covariantName;

            $message = sprintf(
                "Cannot pass object of instance %s to covariant method %s::%s.",
                $targetClass,
                $subjectClass,
                $covariantName
            );
            throw new BadMethodCallException($message);
            // NOTE: this isn't too bad, but perhaps only useful
            // when the program is statically checked.
            // trigger_error($message, E_USER_ERROR);
            // exit;
        }

        if ($handler instanceof Closure) {
            return $handler->__invoke(
                $parameter,
                ...$additionalArgs
            );
        }
        return call_user_func_array(
            $handler,
            $additionalArgs
        );
    }

    public function findHandlerLineage(
        $possibleType,
        $covariantBody,
        $callSubject = null
    ) {
        $className = $this->className($possibleType);
        $candidates = class_parents($className);

        array_unshift($candidates, $className);
        foreach ($candidates as $candidateName) {
            $methodName = $this->methodName(
                $covariantBody,
                $candidateName
            );

            if (method_exists($candidateName, $methodName)) {
                if ($callSubject
                    and $callSubject != $candidateName
                ) {
                    return false;
                }
                return [$possibleType, $methodName];
            }
        }
        return false;
    }

    public function className($possibleType)
    {
        if (is_string($possibleType)) {
            return $possibleType;
        }
        return get_class($possibleType);

    }

    public function methodName($methodName, $className)
    {
        $className = trim(
            substr(
                $className,
                strrpos($className, '\\')
            ),
            '\\'
        );
        return $methodName . $className;
    }
}
