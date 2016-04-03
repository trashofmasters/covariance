<?php

namespace Ofc\Covariance;

use RuntimeException;

class CovariantCall
{
    /**
     * Dispatch table
     *
     * @type array
     */
    private $dispatchTable = [];

    /**
     * Name of the method being augmenting.
     *
     * @type string
     */
    private $covariantMethod;

    /**
     * Name of the class on which the covariant method
     * is actually called. This is relevant for polymorphic
     * calls.
     *
     * @type string
     */
    private $subjectType;

    public function __construct($covariantMethod)
    {
        $this->covariantMethod = $covariantMethod;
    }

    /**
     * @return string
     */
    public function getParameterType()
    {
        return $this->parameterType;
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

    public function setSubjectType($possibleType)
    {
        $this->subjectType = $this->className($possibleType);
        return $this;
    }

    public function execute($parameter, ...$additionalArgs)
    {
        $handler = $this->getHandlerForType($parameter);
        if (empty($handler)) {
            $handler = $this->findHandlerLineage(
                $parameter,
                $this->covariantMethod,
                $this->subjectType
            );
            array_unshift($additionalArgs, $parameter);
        }

        if (empty($handler)) {
            $targetClass = $this->className($parameter);
            $subjectClass = $this->subjectType;
            $covariantMethod = $this->covariantMethod;

            $message = sprintf(
                "Cannot pass object of instance %s to covariant method %s::%s.",
                $subjectClass,
                $targetClass,
                $covariantMethod
            );
            trigger_error($message, E_USER_ERROR);
            exit;
        }

        if ($handler instanceof \Closure) {
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
        $covariantMethod,
        $subjectType = null
    ) {
        $className = $this->className($possibleType);
        $candidates = class_parents($className);

        array_unshift($candidates, $className);
        foreach ($candidates as $candidateName) {
            $methodName = $this->methodName(
                $covariantMethod,
                $candidateName
            );

            if (method_exists($candidateName, $methodName)) {
                if ($subjectType
                    and $subjectType != $candidateName
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
