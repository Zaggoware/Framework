<?php

namespace Zaggoware\Reflection;

class ParameterInfo implements IParameterInfo {

    public function __construct(Type $type, $name, MethodInfo $methodInfo, \ReflectionParameter $reflectionParameter) {
        $this->type = $type;
        $this->name = $name;
        $this->methodInfo = $methodInfo;
        $this->reflectionParameter = $reflectionParameter;
    }

    /** @var Type */
    private $type;

    /** @var string */
    private $name;

    /** @var MethodInfo */
    private $methodInfo;

    /** @var \ReflectionParameter */
    private $reflectionParameter;

    /**
     * @return bool
     */
    function isOptional() {
        return $this->reflectionParameter->isOptional();
    }

    /**
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    function getAccessModifier() {
        return null;
    }

    /**
     * @return bool
     */
    function isPrivate() {
        return false;
    }

    /**
     * @return bool
     */
    function isPublic() {
        return false;
    }

    /**
     * @return bool
     */
    function isProtected() {
        return false;
    }

    /**
     * @return bool
     */
    function isStatic() {
        return false;
    }
}