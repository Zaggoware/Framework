<?php

namespace Zaggoware\Mapper;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\IDictionary;
use Zaggoware\Reflection\Type;

class Profile {
    /** @var Type */
    protected $sourceType;

    /** @var Type */
    protected $destinationType;

    /** @var IDictionary<string, PropertyProfile> */
    protected $propertyProfiles;

    /**
     * Profile constructor.
     * @param Type $sourceType
     * @param Type $destinationType
     * @throws ArgumentNullException
     */
    public function __construct(Type $sourceType, Type $destinationType) {
        if ($sourceType === null) {
            throw new ArgumentNullException("sourceType");
        }

        if ($destinationType === null) {
            throw new ArgumentNullException("destinationType");
        }

        $this->sourceType = $sourceType;
        $this->destinationType = $destinationType;
        $this->propertyProfiles = new Dictionary();
    }

    /**
     * @return Type
     */
    public function getSourceType() {
        return $this->sourceType;
    }

    /**
     * @return Type
     */
    public function getDestinationType() {
        return $this->destinationType;
    }

    /**
     * @return IDictionary
     */
    public function getPropertyProfiles() {
        return $this->propertyProfiles;
    }
}