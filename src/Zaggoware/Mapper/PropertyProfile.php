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

    /**
     * PropertyProfile constructor.
     * @param Type $sourceType
     * @param PropertyInfo $propertyInfo
     * @param Type $destinationType
     * @param bool $isIgnored
     * @throws ArgumentNullException
     */
    public function __construct(Type $sourceType, PropertyInfo $propertyInfo, Type $destinationType, $isIgnored) {
        parent::__construct($sourceType, $destinationType);
        
        $this->propertyInfo = $propertyInfo;
        $this->propertyProfiles = new Dictionary();
        $this->isIgnored = $isIgnored;
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
}