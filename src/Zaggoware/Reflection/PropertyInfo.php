<?php


namespace Zaggoware\Reflection;


use Zaggoware\Helpers\TypeHelper;

class PropertyInfo implements IProperty {

    /**
     * @param Type $type
     * @param string $name
     */
    public function __construct(Type $type, $name) {
        $this->type = $type;
        $this->name = $name;
    }

    /** @var Type */
    private $type;

    /** @var string */
    private $name;

    /** @var FieldInfo */
    private $field;

    /** @var MethodInfo|bool */
    private $getterMethod = false;

    /** @var MethodInfo|bool */
    private $setterMethod = false;

    /**
     * @return bool
     */
    public function hasGetterMethod() {
        if ($this->getterMethod === false) {
            $uName = ucfirst($this->name);
            return $this->type->hasMethod("get". $uName)
                || $this->type->hasMethod("is". $uName)
                || $this->type->hasMethod("has". $uName);
        }

        return $this->getterMethod !== null;
    }

    /**
     * @return MethodInfo
     */
    public function getGetterMethod() {
        if ($this->getterMethod === false) {
            $uName = ucfirst($this->name);
            $getterMethod = $this->type->getMethod("get". $uName);
            $getterMethod = $getterMethod === null ? $this->type->getMethod("is". $uName) : $getterMethod;
            $getterMethod = $getterMethod === null ? $this->type->getMethod("has". $uName) : $getterMethod;

            $this->getterMethod = $getterMethod;
        }

        return $this->getterMethod;
    }

    /**
     * @return bool
     */
    public function hasSetterMethod() {
        if ($this->setterMethod === false) {
            return $this->type->hasMethod("set". ucfirst($this->name));
        }

        return $this->setterMethod !== null;
    }

    /**
     * @return MethodInfo
     */
    public function getSetterMethod() {
        if ($this->setterMethod === false) {
            $this->setterMethod = $this->type->getMethod("set". ucfirst($this->name));
        }

        return $this->setterMethod;
    }

    /**
     * @return bool
     */
    public function isReadOnly() {
        return $this->hasGetterMethod() && !$this->hasSetterMethod();
    }

    /**
     * @return bool
     */
    public function isWriteOnly() {
        return !$this->hasGetterMethod() && $this->hasSetterMethod();
    }

    /**
     * @return FieldInfo
     */
    public function getField() {
        if ($this->field === null) {
            $this->field = new FieldInfo($this->type, $this->name);
        }

        return $this->field;
    }

    /**
     * @param object $instance
     * @return mixed
     * @throws \ReflectionException
     */
    public function getValue($instance) {
        $instanceType = new Type($instance);
        if ($instanceType->isBuiltInType()) {
            throw new \ReflectionException("Cannot invoke on built-in types.");
        }

        if (!$instanceType->equals($this->type) && !$this->type->isDerivedFrom($instanceType)) {
            throw new \ReflectionException("\$instance is not of type '{$this->type->getName()}'.");
        }

        if (!$this->hasGetterMethod()) {
            throw new \ReflectionException("Property does not have a getter method.");
        }

        $getterMethod = $this->getGetterMethod();
        if ($getterMethod->hasParameters()) {
            throw new \ReflectionException("Cannot invoke getter-method. Method must be parameterless.");
        }

        return $getterMethod->invoke($instance);
    }

    public function setValue($instance, $value) {
        $instanceType = new Type($instance);
        if ($instanceType->isBuiltInType()) {
            throw new \ReflectionException("Cannot invoke on built-in types.");
        }

        if (!$instanceType->equals($this->type) && !$this->type->isDerivedFrom($instanceType)) {
            throw new \ReflectionException("\$instance is not of type '{$this->type->getName()}'.");
        }

        if (!$this->hasSetterMethod()) {
            throw new \ReflectionException("Property does not have a setter method.");
        }

        $setterMethod = $this->getSetterMethod();
        if (!$setterMethod->hasParameters()) {
            throw new \ReflectionException("Cannot invoke setter-method. Missing value parameter.");
        }

        if ($setterMethod->getParameters()->count() > 1) {
            throw new \ReflectionException("Cannot invoke setter-method. Only 1 parameter is supported.");
        }

        $setterMethod->invoke($instance, array($value));
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
        // TODO: implement
        return null;
    }

    /**
     * @return bool
     */
    public function isPrivate() {
        // TODO: implement
        return false;
    }

    /**
     * @return bool
     */
    public function isPublic() {
        // TODO: implement
        return false;
    }

    /**
     * @return bool
     */
    public function isProtected() {
        // TODO: implement
        return false;
    }

    /**
     * @return bool
     */
    public function isStatic() {
        // TODO: implement
        return false;
    }
}