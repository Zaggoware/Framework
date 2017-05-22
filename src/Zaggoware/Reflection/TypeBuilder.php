<?php

namespace Zaggoware\Reflection;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\DuplicateKeyException;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Exceptions\NotImplementedException;
use Zaggoware\Exceptions\NotSupportedException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\IEnumerable;

class TypeBuilder implements IReflectBuilder {
    /**
     * @param string $fullName
     */
    public function __construct($fullName = null) {
        if (!empty($fullName)) {
            $this->setName($fullName);
        }

        $this->constructorParams = new Dictionary();
        $this->properties = new Dictionary();
        $this->methods = new Dictionary();
        $this->fields = new Dictionary();
        $this->referencedTypes = new ArrayList();
    }

    /** @var string */
    private $typeName;

    /** @var string */
    private $namespace;

    /** @var bool */
    private $isFinal = false;

    /** @var bool */
    private $isInterface = false;

    /** @var bool */
    private $hasConstructor = false;

    /** @var Dictionary<string, PropertyBuilder> */
    private $properties;

    /** @var Dictionary<string, MethodBuilder> */
    private $methods;

    /** @var Dictionary<string, FieldBuilder> */
    private $fields;

    /** @var ArrayList<Type> */
    private $referencedTypes;

    /**
     * @return string
     */
    public function getName() {
        return $this->typeName;
    }

    /**
     * @param string $className
     * @return $this
     */
    public function setName($className) {
        if (empty($className)) {
            throw new \InvalidArgumentException("className cannot be empty.");
        }

        if (strpos($className, "\\") !== false) {
            $this->namespace = $this->explodeNamespace($className);
        }

        $this->typeName = $this->explodeTypeName($className);

        return $this;
    }

    public function getFullName() {
        return $this->getNamespace() . Type::NAMESPACE_SEPARATOR . $this->getName();
    }

    /**
     * @return bool
     */
    public function isInterface() {
        return $this->isInterface;
    }

    /**
     * @return $this
     */
    public function makeInterface() {
        $this->isInterface = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFinal() {
        return $this->isFinal;
    }

    /**
     * @return $this
     */
    public function makeFinal() {
        $this->isFinal = true;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType() {
        throw new NotImplementedException();
    }

    /**
     * @param Type $type
     * @return $this
     * @throws NotSupportedException
     * @deprecated Type cannot be set, as it doesn't exist (yet). Use 'setName($name)' instead.
     */
    public function setType(Type $type) {
        throw new NotImplementedException();
    }

    /**
     * @return string
     */
    public function getNamespace()  {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return $this
     * @throws ArgumentNullException
     */
    public function setNamespace($namespace)  {
        if (empty($namespace) || preg_match("/^\\s*$/s", $namespace)) {
            $this->namespace = "\\";

            return $this;
        }

        if (!preg_match("/[a-z0-9_\\\\]/si", $namespace)) {
            throw new \InvalidArgumentException("Namespace contains illegal characters. Allowed characters are: a-z, A-Z, 0-9, _ and \\");
        }

        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @param MethodBuilder $methodBuilder
     * @throws DuplicateKeyException
     * @return $this
     */
    public function setConstructor(MethodBuilder $methodBuilder) {
        $name = $methodBuilder->getName();

        if (!empty($name) && !$this->isConstructor($name)) {
            throw new \InvalidArgumentException("The name for the method must be 'constructor', 'ctor', '__construct' or '{$this->typeName}'.'");
        }

        if ($this->methods->containsKey($this->typeName)) {
            $this->methods->remove($this->typeName);
        }

        $this->hasConstructor = true;

        $methodBuilder->setName("__construct");

        $this->internalAddMethod($methodBuilder);

        return $this;
    }

    /**
     * @return MethodBuilder|null
     */
    public function getConstructor() {
        if (!$this->methods->containsKey($this->typeName)) {
            return null;
        }

        return $this->methods[$this->typeName];
    }

    /**
     * @param Type|string $type
     * @return $this
     */
    public function referenceType($type) {
        if (!is_string($type) && !($type instanceof Type)) {
            throw new InvalidArgumentTypeException("type", $type, "Type or string");
        }

        if (is_string($type)) {
            $type = new Type($type);
        }

        if (!$this->isReferenced($type)) {
            $this->referencedTypes->add($type);
        }

        return $this;
    }

    /**
     * @param IEnumerable|\ArrayIterator|array $types
     * @return $this
     */
    public function referenceTypes($types) {
        foreach ($types as $type) {
            $this->referenceType($type);
        }

        return $this;
    }

    /**
     * @return IEnumerable<Type>
     */
    public function getReferencedTypes() {
        return $this->referencedTypes->asEnumerable();
    }

    /**
     * @param PropertyBuilder $propertyBuilder
     * @return $this
     * @throws DuplicateKeyException
     */
    public function addProperty(PropertyBuilder $propertyBuilder) {
        $name = $propertyBuilder->getName();

        if ($this->properties->containsKey($name)) {
            throw new DuplicateKeyException($name);
        }

        if ($this->fields->containsKey($propertyBuilder->getField()->getName())) {
            throw new DuplicateKeyException("Field with name '$name' already exists.");
        }

        if (($type = $propertyBuilder->getType()) !== null && $type->getNamespace() !== $this->getNamespace()) {
            $this->referenceType($type);
        }

        $this->properties->add($name, $propertyBuilder);

        return $this;
    }

    /**
     * @return IEnumerable
     */
    public function getProperties() {
        return $this->properties->asEnumerable();
    }

    /**
     * @param MethodBuilder $methodBuilder
     * @return $this
     * @throws DuplicateKeyException
     */
    public function addMethod(MethodBuilder $methodBuilder) {
        $name = $methodBuilder->getName();

        if ($this->methods->containsKey($name)) {
            throw new DuplicateKeyException($name);
        }

        if ($this->isConstructor($name)) {
            $this->setConstructor($methodBuilder);
            return $this;
        }

        $this->internalAddMethod($methodBuilder);

        return $this;
    }

    /**
     * @param IEnumerable|\IteratorAggregate|array|MethodBuilder $methodBuilders
     * @param MethodBuilder|null $_
     * @throws DuplicateKeyException
     * @return $this
     */
    public function addMethods($methodBuilders, $_ = null) {
        $funcArgs = func_get_args();

        if (count($funcArgs) > 1) {
            $methodBuilders = $funcArgs;
        } else if ($methodBuilders instanceof MethodBuilder) {
            $methodBuilders = array($methodBuilders);
        }

        foreach ($methodBuilders as $method) {
            if (!($method instanceof MethodBuilder)) {
                throw new InvalidArgumentTypeException("method", $method, new Type("MethodBuilder"));
            }

            $this->addMethod($method);
        }

        return $this;
    }

    /**
     * @return IEnumerable
     */
    public function getMethods() {
        $typeBuilder = $this;

        return $this->methods->where(function(MethodBuilder $methodBuilder) use ($typeBuilder){
            return !$typeBuilder->isConstructor($methodBuilder->getName());
        })->asEnumerable();
    }

    /**
     * @param FieldBuilder $fieldBuilder
     * @return $this
     * @throws DuplicateKeyException
     */
    public function addField(FieldBuilder $fieldBuilder) {
        $name = $fieldBuilder->getName();

        if ($this->fields->containsKey($name)) {
            throw new DuplicateKeyException($name);
        }

        if (($type = $fieldBuilder->getType()) !== null && $type->getNamespace() !== $this->getNamespace()) {
            $this->referenceType($type);
        }

        $this->fields->add($name, $fieldBuilder);

        return $this;
    }

    /**
     * @param IEnumerable|\IteratorAggregate|array|MethodBuilder... $fieldBuilders
     * @param MethodBuilder...|null $_
     * @return $this
     */
    public function addFields($fieldBuilders, $_ = null) {
        $funcArgs = func_get_args();

        if (count($funcArgs) > 1) {
            $fieldBuilders = $funcArgs;
        } else if ($fieldBuilders instanceof FieldBuilder) {
            $fieldBuilders = array($fieldBuilders);
        }

        foreach ($fieldBuilders as $field) {
            if (!($field instanceof FieldBuilder)) {
                throw new InvalidArgumentTypeException("field", $field, new Type("FieldBuilder"));
            }

            $this->addField($field);
        }

        return $this;
    }

    /**
     * @return IEnumerable
     */
    public function getFields() {
        return $this->fields->asEnumerable();
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
     * @deprecated Access modifier cannot be set on Type level.
     */
    public function setAccessModifier($accessModifier) {
        return $this;
    }

    protected function isConstructor($name) {
        return $name == "__construct" || $name == "ctor" || $name == "constructor" || $name === $this->typeName;
    }

    protected function isReferenced(Type $type) {
        if ($type->isBuiltInType()) {
            return true;
        }

        if ($this->namespace === $type->getNamespace()) {
            return true;
        }

        return $this->referencedTypes->any(function(Type $refed) use ($type) {
            return $refed->getName() === $type->getName();
        });
    }

    private function explodeTypeName($fullClassName) {
        $sep = strrpos($fullClassName, Type::NAMESPACE_SEPARATOR);

        return $sep !== false ? substr($fullClassName, $sep + 1) : $fullClassName;
    }

    private function explodeNamespace($fullClassName) {
        $sep = strrpos($fullClassName, Type::NAMESPACE_SEPARATOR);

        return $sep !== false ? substr($fullClassName, 0, $sep) : Type::NAMESPACE_SEPARATOR;
    }

    private function internalAddMethod(MethodBuilder $methodBuilder) {
        $name = $methodBuilder->getName();

        if ($this->methods->containsKey($name)) {
            throw new DuplicateKeyException($name);
        }

        $typeBuilder = $this;
        $types = $methodBuilder->getParameters()->where(function(MethodParameterBuilder $param) use ($typeBuilder) {
            return !$typeBuilder->isReferenced($param->getType());
        })->select(function(MethodParameterBuilder $param) {
            return $param->getType();
        });

        if ($types->any()) {
            $types->each(function(Type $type) use ($typeBuilder) {
                if ($type !== null && $type->getNamespace() !== $typeBuilder->getNamespace()) {
                    $typeBuilder->referenceType($type);
                }
            });
        }

        if (($returnType = $methodBuilder->getType()) !== null && $returnType->getNamespace() !== $this->getNamespace()) {
            $this->referenceType($returnType);
        }

        $this->methods->add($name, $methodBuilder);
    }
}