<?php


namespace Zaggoware\Reflection;


use Zaggoware\Exceptions\InvalidArgumentTypeException;

class FieldBuilder implements IReflectBuilder {
    /**
     * @param string $name
     */
    public function __construct($name = null) {
        $this->name = $name;
        $this->accessModifier = AccessModifier::FIELD_DEFAULT;
    }

    /** @var string */
    private $name;

    /** @var Type */
    private $type;

    /** @var string */
    private $accessModifier;


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
     * @return Type
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param Type $type
     * @return $this
     */
    public function setType(Type $type) {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessModifier() {
        return $this->accessModifier;
    }

    /**
     * @param string $accessModifier See AccessModifier.
     * @return $this
     */
    public function setAccessModifier($accessModifier) {
        if (!AccessModifier::validate($accessModifier)) {
            throw new \InvalidArgumentException("'$accessModifier' is not a valid access modifier.");
        }

        $this->accessModifier = $accessModifier;

        return $this;
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
        $this->hasDefaultValue = true;
        $this->useLiteralStringValueForDefaultValue = $useLiteralStringValue;
        $this->defaultValue = $defaultValue;

        return $this;
    }
}