<?php

namespace Zaggoware\Reflection;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Helpers\ReflectionHelper;
use Zaggoware\Helpers\StringHelper;

class MethodInfo implements IMethod {
    public function __construct(Type $type, $name, \ReflectionMethod $reflectionMethod = null) {
        $this->type = $type;
        $this->name = $name;

        if ($reflectionMethod !== null) {
            if ($reflectionMethod->getName() !== $name) {
                throw new \InvalidArgumentException("reflectionMethod");
            }

            $this->reflectionMethod = $reflectionMethod;
        }
    }

    /** @var Type */
    protected $type;

    /** @var string */
    protected $name;

    /** @var string */
    private $accessModifier;

    /** @var string */
    private $methodType;

    /** @var bool|null */
    private $isGetterMethod = null;

    /** @var bool|null */
    private $isSetterMethod = null;

    /** @var \ReflectionMethod */
    private $reflectionMethod;

    /**
     * @return Type
     */
    public function getDeclaringType() {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isGetterMethod() {
        if ($this->isGetterMethod === null) {
            $this->isGetterMethod = StringHelper::startsWith($this->name, "get", true) &&
                $this->type->hasField(lcfirst(substr($this->name, 3)));
        }

        return $this->isGetterMethod;
    }

    /**
     * @return bool
     */
    public function isSetterMethod() {
        if ($this->isSetterMethod === null) {
            $this->isSetterMethod = StringHelper::startsWith($this->name, "set", true) &&
                $this->type->hasField(lcfirst(substr($this->name, 3)));
        }

        return $this->isSetterMethod;
    }

    /**
     * @return bool
     */
    public function hasParameters() {
        return $this->getParameters()->any();
    }

    /**
     * @return ArrayList
     */
    public function getParameters() {
        $parameters = (new ArrayList($this->getReflectionMethod()->getParameters()))
            ->select(function(\ReflectionParameter $reflectionParameter) {
                return new ParameterInfo($this->type, $reflectionParameter->getName(), $this, $reflectionParameter);
            });

        return $parameters;
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
        if ($this->accessModifier === null) {
            $this->accessModifier = AccessModifier::PUBLIC_M;
            $reflectionMethod = $this->getReflectionMethod();

            if ($reflectionMethod->isProtected()) {
                $this->accessModifier = AccessModifier::PROTECTED_M;
            } else if ($reflectionMethod->isPrivate()) {
                $this->accessModifier = AccessModifier::PRIVATE_M;
            }
        }

        return $this->accessModifier;
    }

    /**
     * @return bool
     */
    public function isPrivate() {
        return $this->getAccessModifier() === AccessModifier::PRIVATE_M;
    }

    /**
     * @return bool
     */
    public function isPublic() {
        return $this->getAccessModifier() === AccessModifier::PUBLIC_M;
    }

    /**
     * @return bool
     */
    public function isProtected() {
        return $this->getAccessModifier() === AccessModifier::PROTECTED_M;
    }

    /**
     * @return string
     */
    public function getMethodType() {
        if ($this->methodType === null) {
            $this->methodType = MethodType::DEFAULT_METHOD;
            $reflectionMethod = $this->getReflectionMethod();

            if ($reflectionMethod->isStatic()) {
                $this->methodType = MethodType::STATIC_METHOD;
            } else if ($reflectionMethod->isAbstract()) {
                $this->methodType = MethodType::ABSTRACT_METHOD;
            }
        }

        return $this->methodType;
    }

    /**
     * @return bool
     */
    public function isAbstract() {
        return $this->getMethodType() === MethodType::ABSTRACT_METHOD;
    }

    /**
     * @return bool
     */
    public function isStatic() {
        return $this->getMethodType() === MethodType::STATIC_METHOD;
    }

    /**
     * @param mixed $instance
     * @param array|mixed|null $args
     * @return mixed
     * @throws \ReflectionException
     */
    public function invoke($instance, $args = null) {
        return ReflectionHelper::invokeMethod($instance, $this->name, $args);
    }

    /**
     * @return \ReflectionMethod
     */
    private function getReflectionMethod() {
        if ($this->reflectionMethod === null) {
            $this->reflectionMethod = new \ReflectionMethod($this->type->getClass()->getName(), $this->name);
        }

        return $this->reflectionMethod;
    }
}