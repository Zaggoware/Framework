<?php

namespace Zaggoware\Mapper;

use Zaggoware\Generic\KeyValuePair;
use Zaggoware\Reflection\PropertyInfo;
use Zaggoware\Reflection\Type;

class PropertyProfileBuilder extends ProfileBuilder {
    /** @var PropertyInfo */
    private $propertyInfo;

    /** @var bool */
    private $isIgnored;

    /** @var callable */
    private $valueSetter;

    public function __construct(Type $sourceType, PropertyInfo $propertyInfo, $destination) {
        parent::__construct($sourceType, $destination);

        $this->propertyInfo = $propertyInfo;
    }

    public function ignore() {
        $this->isIgnored = true;

        return $this;
    }

    public function useValue(callable $valueSetter) {
        $this->valueSetter = $valueSetter;

        return $this;
    }

    /*
     * TODO: Extend: able to specify name differences, able to override value, etc.
     */

    /**
     * @return PropertyProfile
     * @throws \Exception
     */
    public function getProfile() {
        if (!$this->isIgnored && $this->destinationType === null) {
            throw new \Exception("Cannot create profile without destination type.");
        }

        $profile = new PropertyProfile($this->sourceType, $this->propertyInfo, $this->destinationType, $this->isIgnored, $this->valueSetter);

        $this->addPropertyProfiles($profile);

        return $profile;
    }
}