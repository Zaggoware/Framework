<?php

namespace Zaggoware\Mapper;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Reflection\PropertyInfo;
use Zaggoware\Reflection\Type;

class PropertyProfile extends Profile {
    /** @var PropertyInfo */
    private $propertyInfo;

    /** @var bool */
    private $isIgnored = false;

    /** @var callable */
    private $valueSetter;

    /**
     * PropertyProfile constructor.
     * @param Type $sourceType
     * @param PropertyInfo $propertyInfo
     * @param Type $destinationType
     * @param bool $isIgnored
     * @param callable $valueSetter
     * @throws ArgumentNullException
     */
    public function __construct(Type $sourceType, PropertyInfo $propertyInfo, Type $destinationType, $isIgnored, callable $valueSetter = null) {
        parent::__construct($sourceType, $destinationType);

        $this->propertyInfo = $propertyInfo;
        $this->propertyProfiles = new Dictionary();
        $this->isIgnored = $isIgnored;
        $this->valueSetter = $valueSetter;
    }

    /**
     * @return bool
     */
    public function isIgnored() {
        return $this->isIgnored;
    }

    /**
     * @param bool $flag
     */
    public function setIsIgnored($flag) {
        $this->isIgnored = $flag;
    }

    public function hasValueSetter() {
        return $this->valueSetter !== null;
    }

    public function invokeValueSetter($originalValue) {
        return call_user_func_array($this->valueSetter, array($originalValue));
    }
}