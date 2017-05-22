<?php

namespace Zaggoware\Reflection;

use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Helpers\TypeHelper;

final class Type extends \stdClass implements IType {
    const NAMESPACE_SEPARATOR = "\\";

    /**
     * @param object|string $value
     */
    public function __construct($value) {
        if (!is_string($value)) {
            if (!TypeHelper::isBuiltInType($value)) {
                $value = get_class($value);

                if (strpos($value, "\\") === false) {
                    $value = "\\". $value;
                }
            } else {
                $value = gettype($value);
            }
        }

        $isStringType = TypeHelper::isString($value);

        if (!$isStringType && !TypeHelper::isBuiltInType($value) && strtolower($value) !== "object") {
            if (!class_exists($value)) {
                throw new \InvalidArgumentException("Type '$value' could not be found.");
            }

            $this->reflectionClass = new \ReflectionClass($value);
        } else {
            if ($isStringType) {
                $value = "string";
            }

            $this->builtInType = TypeHelper::normalizeBuiltInType($value);
        }
    }

    /** @var \ReflectionClass */
    private $reflectionClass;

    /** @var string */
    private $builtInType;

    /**
     * @return Type
     */
    public static function type() {
        return new Type(__CLASS__);
    }

    /**
     * @return Type
     */
    public static function object() {
        return new Type("object");
    }

    /**
     * @return Type
     */
    public static function arrayType() {
        // The method is called 'arrayType' because 'array' alone is seen as a keyword.
        return new Type("array");
    }

    /**
     * @return Type
     */
    public static function integer() {
        return new Type("integer");
    }

    /**
     * @return Type
     */
    public static function boolean() {
        return new Type("boolean");
    }

    /**
     * @return Type
     */
    public static function string() {
        return new Type("string");
    }

    /**
     * @return Type
     */
    public static function float() {
        return new Type("float");
    }

    /**
     * @return Type
     */
    public static function double() {
        return new Type("double");
    }

    /**
     * @return Type
     */
    public static function null() {
        return new Type("null");
    }

    /**
     * @return Type
     */
    public static function void() {
        return new Type("void");
    }

    public static function separateClassNameFromNamespace($fullName) {
        $index = strrpos($fullName, self::NAMESPACE_SEPARATOR);
        if ($index === false) {
            return array(
                "namespace" => "",
                "className" => $fullName
            );
        }

        return array(
            "namespace" => substr($fullName, 0, $index),
            "className" => substr($fullName, $index + 1)
        );
    }

    /**
     * @return \ReflectionClass
     */
    public function getClass() {
        return $this->reflectionClass;
    }

    /**
     * @param array $args
     * @return object
     */
    public function createInstance(array $args = array()) {
        if ($this->isBuiltInType()) {
            throw new \BadMethodCallException("Cannot create instance of a built-in type.");
        }

        if(empty($args)) {
            return $this->reflectionClass->newInstance();
        }

        return $this->reflectionClass->newInstanceArgs($args);
    }

    /**
     * @return mixed
     */
    function isInterface() {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->reflectionClass->isInterface();
    }

    /**
     * @return bool
     */
    function isClass() {
        if ($this->isBuiltInType()) {
            return false;
        }

        return !$this->reflectionClass->isInterface();
    }

    /**
     * @return bool
     */
    public function isAbstract() {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->reflectionClass->isAbstract();
    }

    /**
     * @return bool
     */
    public function isFinal() {
        if ($this->isBuiltInType()) {
            return true;
        }

        return $this->reflectionClass->isFinal();
    }

    /**
     * @return bool
     */
    public function isValueType() {
        return TypeHelper::isValueType($this->getName());
    }

    /**
     * @return bool
     */
    public function isNullable() {
        if ($this->isValueType()) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $object
     * @return bool
     */
    public function isInstance($object) {
        return $this->equals(new Type($object));
    }

    /**
     * @return string
     */
    public function getName() {
        if ($this->isBuiltInType()) {
            return $this->builtInType;
        }

        return $this->reflectionClass->getName();
    }

    /**
     * @return string
     */
    public function getNamespace() {
        if ($this->isBuiltInType()) {
            return "";
        }

        $ns = $this->reflectionClass->getNamespaceName();

        if (empty($ns)) {
            return self::NAMESPACE_SEPARATOR;
        }

        return $ns;
    }

    /**
     * @return string
     */
    public function getShortName() {
        if ($this->isBuiltInType()) {
            return $this->builtInType;
        }

        return $this->reflectionClass->getShortName();
    }

    /**
     * @return bool
     */
    public function isInstantiable() {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->reflectionClass->isInstantiable();
    }

    /**
     * @return bool
     */
    public function hasConstructor() {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->reflectionClass->getConstructor() !== null;
    }

    /**
     * @return \ReflectionMethod
     */
    public function getConstructor() {
        if ($this->isBuiltInType()) {
            throw new \BadMethodCallException("Cannot get constructor for a built-in type.");
        }

        return $this->reflectionClass->getConstructor();
    }


    /**
     * @param string $name
     * @return MethodInfo|null
     */
    public function getMethod($name) {
        if ($this->isBuiltInType()) {
            throw new \BadMethodCallException("Cannot get method for a built-in type.");
        }

        if (!$this->reflectionClass->hasMethod($name)) {
            return null;
        }

        return new MethodInfo($this, $name);
    }

    /**
     * @param mixed $filter
     * @return IEnumerable<MethodInfo>
     */
    public function getMethods($filter = null) {
        $methods = new ArrayList(
            $filter !== null
                ? $this->reflectionClass->getMethods($filter)
                : $this->reflectionClass->getMethods());

        $type = $this;

        return $methods->select(function(\ReflectionMethod $method) use ($type) {
            return new MethodInfo($type, $method->getName(), $method);
        });
    }

    /**
     * @param string $name
     * @return FieldInfo|null
     */
    public function getField($name) {
        if (!$this->reflectionClass->hasProperty($name)) {
            return null;
        }

        return new FieldInfo($this, $name);
    }

    /**
     * @param mixed $filter
     * @return IEnumerable<FieldInfo>
     */
    public function getFields($filter = null) {
        $fields = new ArrayList(
            $filter !== null
                ? $this->reflectionClass->getProperties($filter)
                : $this->reflectionClass->getProperties());

        $type = $this;

        return $fields->select(function(\ReflectionProperty $field) use ($type) {
            return new FieldInfo($type, $field->getName());
        });
    }

    /**
     * @param string $name
     * @return  PropertyInfo|null
     */
    public function getProperty($name) {
        if (!$this->hasField($name)) {
            return null;
        }

        if (!$this->hasMethod("get". ucfirst($name))
            && !$this->hasMethod("set". ucfirst($name))) {
            return null;
        }

        return new PropertyInfo($this, $name);
    }

    /**
     * @param mixed $filter
     * @return IEnumerable<PropertyInfo>
     */
    public function getProperties($filter = null) {
        $properties = new ArrayList(
            $filter !== null
                ? $this->reflectionClass->getProperties($filter)
                : $this->reflectionClass->getProperties());
        $type = $this;

        return $properties->where(function(\ReflectionProperty $reflectionProperty) use($type) {
            $name = $reflectionProperty->getName();

            return $type->hasMethod("get". ucfirst($name))
                || $type->hasMethod("set". ucfirst($name));
        })->select(function(\ReflectionProperty $reflectionProperty) use($type) {
            return new PropertyInfo($type, $reflectionProperty->getName());
        });
    }

    /**
     * @param string $name
     * @return \ReflectionMethod|\ReflectionProperty|null
     */
    public function getMember($name) {
        $field = $this->getField($name);
        if ($field !== null) {
            return $field;
        }

        $property = $this->getProperty($name);
        if ($property !== null) {
            return $property;
        }

        return $this->getMethod($name);
    }

    /**
     * @param mixed $filter
     * @return IEnumerable
     */
    public function getMembers($filter = null) {
        $fields = $this->getFields($filter);
        $properties = $this->getProperties($filter);
        $methods = $this->getMethods($filter);

        return $fields->union($properties, $methods);
    }

    /**
     * @param mixed $obj
     * @return bool
     */
    public function equals($obj) {
        if ($obj instanceof Type) {
            if (strtolower($obj->getName()) === "object") {
                return !$this->isValueType();
            }

            if (strtolower($obj->getName()) === "null") {
                return $this->isNullable();
            }

            return strtolower($obj->getName()) === strtolower($this->getName());
        }

        if (is_null($obj)) {
            return $this->isNullable() || strtolower($this->getName()) === "null";
        }

        if (is_string($obj)) {
            if (strpos($obj, "\\") === false) {
                return strtolower($obj) === strtolower($this->getShortName());
            }

            return strtolower($obj) === strtolower($this->getName());
        }

        return $this === $obj;
    }

    /**
     * @param Type $type
     * @return bool
     */
    public function isDerivedFrom(Type $type) {
        return $this->reflectionClass->isSubclassOf($type->getName());
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getName();
    }

    /**
     * @return bool
     */
    public function isBuiltInType() {
        return $this->builtInType !== null && $this->reflectionClass === null;
    }

    /**
     * @return string
     */
    public function getAccessModifier() {
        return "public";
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
        return true;
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
     * @param string $methodName
     * @return bool
     */
    public function hasMethod($methodName) {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->reflectionClass->hasMethod($methodName);
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function hasProperty($propertyName) {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->getProperty($propertyName) !== null;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function hasField($fieldName) {
        if ($this->isBuiltInType()) {
            return false;
        }

        return $this->reflectionClass->hasProperty($fieldName);
    }

    /**
     * @param string $memberName
     * @return bool
     */
    public function hasMember($memberName) {
        if ($this->isBuiltInType()) {
            return false;
        }

        return  $this->hasProperty($memberName) || $this->hasMethod($memberName) || $this->hasField($memberName);
    }

    /**
     * @return bool
     */
    public function isPhpClass() {
        switch ($this->getName()) {
            case "DateTime":
                return true;
        }

        return false;
    }

}