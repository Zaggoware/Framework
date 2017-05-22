<?php

namespace Zaggoware\Reflection;

use Zaggoware\Exceptions\InvalidArgumentTypeException;

class MethodParameterBuilder implements IReflectBuilder {
    public function __construct($name = null) {
        $this->name = $name;
    }

    /** @var string */
    private $name;

    /** @var Type */
    private $type;

    /** @var bool */
    private $isStronglyTyped = false;

    /** @var bool */
    private $hasDefaultValue = false;

    /** @var  */
    private $defaultValue;

    /** @var bool */
    private $useLiteralStringValueForDefaultValue = false;

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessModifier() {
        return AccessModifier::DEFAULT_M;
    }

    /**
     * @param string $accessModifier See AccessModifier.
     * @return $this
     * @deprecated Cannot set access modifier for method parameter.
     */
    public function setAccessModifier($accessModifier) {
        return $this;
    }

    /**
     * @return Type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param Type $type
     * @param bool $stronglyTyped
     * @return $this
     */
    public function setType(Type $type, $stronglyTyped = true) {
        $this->type = $type;

        if (!$this->type->isBuiltInType()) {
            $this->isStronglyTyped = $stronglyTyped;
        } else {
            $this->isStronglyTyped = false;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isStronglyTyped() {
        return $this->type !== null && $this->isStronglyTyped;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue() {
        return $this->hasDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        if ($this->useLiteralStringValueForDefaultValue) {
            return "\"{$this->defaultValue}\"";
        }

        return $this->defaultValue;
    }

    /**
     * @param string $defaultValue
     * @param bool $useLiteralStringValue
     * @return $this
     */
    public function setDefaultValue($defaultValue, $useLiteralStringValue = false) {
        if ($useLiteralStringValue) {
            $defaultValueType = Type::string();
        } else {
            if (is_string($defaultValue)) {
                $defaultValueType = Type::object();
            } else {
                $defaultValueType = new Type($defaultValue);
            }
        }

        if ($this->type !== null && $this->isStronglyTyped) {
            if ($defaultValue !== null && !$this->type->equals($defaultValueType)) {
                throw new InvalidArgumentTypeException("defaultValue", $defaultValue, $this->type);
            }
        }

        if (is_null($defaultValue)) {
            $defaultValue = "null";
        }

        $this->hasDefaultValue = true;
        $this->useLiteralStringValueForDefaultValue = $useLiteralStringValue;
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeDefaultValue() {
        $this->hasDefaultValue = false;
        $this->defaultValue = null;

        return $this;
    }
}