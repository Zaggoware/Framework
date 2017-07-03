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
     * @return Type
     */
    public function getDeclaringType() {
        return $this->type;
    }

    /**
     * @return Type
     */
    public function getType() {
        if (($c = $this->reflectionParameter->getClass()) === null) {
            return null;
        }

        return new Type($c);
    }

    /**
     * @return bool
     */
    public function isOptional() {
        return $this->reflectionParameter->isOptional();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAccessModifier() {
        return null;
    }

    /**
     * @return bool
     */
    public function isPrivate() {
        return false;
    }

    /**
     * @return bool
     */
    public function isPublic() {
        return false;
    }

    /**
     * @return bool
     */
    public function isProtected() {
        return false;
    }

    /**
     * @return bool
     */
    public function isStatic() {
        return false;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        return $this->reflectionParameter->getDefaultValue();
    }
}