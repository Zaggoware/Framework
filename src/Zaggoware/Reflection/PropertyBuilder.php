<?php


namespace Zaggoware\Reflection;


use Zaggoware\Exceptions\NotSupportedException;
use Zaggoware\Generic\ArrayList;

class PropertyBuilder implements IReflectBuilder {
    /**
     * @param string $name
     */
    public function __construct($name = null) {
        $this->name = $name;
    }

    /** @var string */
    private $name;

    /** @var Type */
    private $type;

    /** @var string */
    private $accessModifier;

    /** @var MethodBuilder */
    private $getterMethod;

    /** @var MethodBuilder */
    private $setterMethod;

    public function getName() {
        return $this->name;
    }

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
    }

    /**
     * @return FieldBuilder
     */
    public function getField() {
        $fieldBuilder = new FieldBuilder($this->name);

        if ($this->type !== null) {
            $fieldBuilder->setType($this->type);
        }

        if (AccessModifier::validate($this->accessModifier)) {
            $fieldBuilder->setAccessModifier($this->accessModifier);
        }

        return $fieldBuilder;
    }

    /**
     * @return bool
     */
    public function hasGetterMethod() {
        return $this->getterMethod !== null;
    }

    /**
     * @return MethodBuilder
     */
    public function getGetterMethod() {
        return $this->getterMethod;
    }

    public function addGetterMethod() {
        if ($this->hasGetterMethod()) {
            throw new NotSupportedException("A getter method was already added.");
        }

        $this->getterMethod = new MethodBuilder("get". ucfirst($this->name));

        if ($this->type !== null) {
            $this->getterMethod->setType($this->type);
        }

        $this->getterMethod->setBody("return \$this->{$this->name};");

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSetterMethod() {
        return $this->setterMethod !== null;
    }

    /**
     * @param bool $stronglyTyped
     * @return $this
     * @throws NotSupportedException
     */
    public function addSetterMethod($stronglyTyped = true) {
        if ($this->hasSetterMethod()) {
            throw new NotSupportedException("A setter method was already added.");
        }

        $methodParam = new MethodParameterBuilder($this->name);

        if ($this->type !== null && $stronglyTyped && !$this->type->isBuiltInType()) {
            $methodParam->setType($this->type);
        }

        $this->setterMethod = new MethodBuilder("set". ucfirst($this->name));

        if ($this->type !== null && $stronglyTyped) {
            $this->setterMethod->setType($this->type);
        }

        $this->setterMethod->setParameters($methodParam)->setBody("\$this->{$this->name} = \${$methodParam->getName()};");

        return $this;
    }

    /**
     * @return MethodBuilder
     */
    public function getSetterMethod() {
        return $this->setterMethod;
    }
}