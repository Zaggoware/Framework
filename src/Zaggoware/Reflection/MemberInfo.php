<?php

namespace Zaggoware\Reflection;

class MemberInfo implements IMember {
    /**
     * @param IType $class
     * @param string $name
     * @param string $memberType See MemberType enum.
     * @param string $accessModifier See AccessModifier enum. Defaults to AccessModifier::PUBLIC.
     * @param bool $isStatic Defaults to false.
     */
    public function __construct(IType $class, $name, $memberType, $accessModifier = AccessModifier::PUBLIC_M, $isStatic = false) {
        $this->class = $class;
        $this->name = $name;
        $this->memberType = $memberType;
        $this->accessModifier = $accessModifier;
        $this->isStatic = $isStatic;
    }

    /** @var IType */
    protected $class;

    /** @var string */
    protected $name;

    /** @var string */
    protected $memberType;

    /** @var string */
    protected $accessModifier;

    /** @var bool */
    protected $isStatic;

    /**
     * @return IType
     */
    public function getClass() {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMemberType() {
        return $this->memberType;
    }

    /**
     * @return bool
     */
    public function isConstructor() {
        return $this->memberType === MemberType::CONSTRUCTOR ||
            ($this->memberType === MemberType::METHOD && $this->name === $this->class->getName());
    }

    /**
     * @return bool
     */
    public function isProperty() {
        return $this->memberType === MemberType::PROPERTY;
    }

    /**
     * @return bool
     */
    public function isMethod() {
        return $this->memberType === MemberType::METHOD;
    }

    /**
     * @return bool
     */
    public function isField() {
        return $this->memberType === MemberType::FIELD;
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
        return $this->accessModifier;
    }

    /**
     * @return bool
     */
    public function isPrivate() {
        return $this->accessModifier === AccessModifier::PRIVATE_M;
    }

    /**
     * @return bool
     */
    public function isPublic() {
        return $this->accessModifier === AccessModifier::PUBLIC_M;
    }

    /**
     * @return bool
     */
    public function isProtected() {
        return $this->accessModifier === AccessModifier::PROTECTED_M;
    }

    /**
     * @return bool
     */
    public function isStatic() {
        return $this->isStatic;
    }
}