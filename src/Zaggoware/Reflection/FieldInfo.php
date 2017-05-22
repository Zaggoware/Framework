<?php

namespace Zaggoware\Reflection;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Helpers\StringHelper;

class FieldInfo implements IField {
    public function __construct(Type $type, $name, \ReflectionProperty $reflectionProperty = null) {
        $this->type = $type;
        $this->name = $name;

        if ($reflectionProperty !== null) {
            if ($reflectionProperty->getName() !== $name) {
                throw new \InvalidArgumentException("reflectionProperty");
            }

            $this->reflectionProperty = $reflectionProperty;
        }
    }

    /** @var Type */
    protected $type;

    /** @var string */
    protected $name;

    /** @var string */
    private $accessModifier;

    /** @var bool */
    private $isStatic;

    /** @var \ReflectionProperty */
    private $reflectionProperty;

    /**
     * @return bool
     */
    public function isPartOfProperty() {
        return (StringHelper::startsWith($this->name, "get") && $this->type->hasMethod("get". ucfirst($this->name)))
            || (StringHelper::startsWith($this->name, "set") && $this->type->hasMethod("set". ucfirst($this->name)));
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
            $reflectionProperty = $this->getReflectionProperty();
            $this->accessModifier = AccessModifier::PUBLIC_M;

            if ($reflectionProperty->isProtected()) {
                $this->accessModifier = AccessModifier::PROTECTED_M;
            } else if ($reflectionProperty->isPrivate()) {
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
     * @return bool
     */
    public function isStatic() {
        if ($this->isStatic === null) {
            $this->isStatic = $this->getReflectionProperty()->isStatic();
        }

        return $this->isStatic;
    }

    /**
     * @param object $instance
     * @throws ArgumentNullException
     * @return mixed
     */
    public function getValue($instance) {
        if ($instance === null) {
            throw new ArgumentNullException("instance");
        }

        $instanceType = new Type($instance);

        if (!$instanceType->equals($this->type)) {
            throw new InvalidArgumentTypeException("instance", $instance, $this->type);
        }

        return $this->getReflectionProperty()->getValue($instance);
    }

    /**
     * @param object $instance
     * @param object $value
     * @throws ArgumentNullException
     */
    public function setValue($instance, $value) {
        if ($instance === null) {
            throw new ArgumentNullException("instance");
        }

        $instanceType = new Type($instance);

        if (!$instanceType->equals($this->type)) {
            throw new InvalidArgumentTypeException("instance", $instance, $this->type);
        }

        $this->getReflectionProperty()->setValue($instance, $value);
    }

    protected function getReflectionProperty() {
        if ($this->reflectionProperty === null) {
            $this->reflectionProperty = new \ReflectionProperty($this->type->getClass()->getName(), $this->name);
        }

        return $this->reflectionProperty;
    }
}