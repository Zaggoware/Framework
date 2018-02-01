<?php

namespace Zaggoware\Mapper;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\DuplicateKeyException;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\KeyValuePair;
use Zaggoware\Reflection\Type;

class ProfileBuilder {
    /** @var Type */
    protected $sourceType;

    /** @var Type */
    protected $destinationType;

    /** @var Dictionary */
    protected $propertyProfileBuilders;

    public function __construct($source, $destination) {
        if ($source === null) {
            throw new ArgumentNullException("source");
        }

        if ($destination === null) {
            throw new ArgumentNullException("destination");
        }

        $this->sourceType = $source instanceof Type ? $source : new Type($source);
        $this->destinationType = $destination instanceof Type ? $destination : new Type($destination);
        $this->propertyProfileBuilders = new Dictionary();
    }

    /**
     * @param Profile $profile
     */
    protected function addPropertyProfiles(Profile $profile) {
        $propertyProfiles = $profile->getPropertyProfiles();
        foreach ($this->propertyProfileBuilders as $pair) {
            /** @var KeyValuePair $pair */

            /** @var string $typeName */
            $typeName = $pair->getKey();

            /** @var PropertyProfileBuilder $profileBuilder */
            $profileBuilder = $pair->getValue();

            $propertyProfiles->add($typeName, $profileBuilder->getProfile());
        }
    }

    /**
     * @param string $propertyName
     * @param object|Type $destination
     * @param callable|null $optionsAction
     * @return PropertyProfileBuilder
     * @throws ArgumentNullException
     * @throws DuplicateKeyException
     */
    public function forProperty($propertyName, $destination, callable $optionsAction = null) {
        if ($propertyName === null) {
            throw new ArgumentNullException("propertyName");
        }

        if ($destination === null && empty($optionsAction)) {
            throw new ArgumentNullException("destination");
        }

        if ($this->propertyProfileBuilders->containsKey($propertyName)) {
            throw new DuplicateKeyException("There is already an existing mapping profile for property '{$propertyName}'.");
        }

        $destinationType = $destination instanceof Type ? $destination : new Type($destination);

        $property = $this->sourceType->getProperty($propertyName);
        if ($property === null) {
            throw new \InvalidArgumentException("Property '{$propertyName}' does not exist on type '{$this->sourceType->getName()}'.");
        }

        $profile = new PropertyProfileBuilder($this->sourceType, $property, $destinationType);
        if (is_callable($optionsAction)) {
            $optionsAction($profile);
        }

        $this->propertyProfileBuilders->add($propertyName, $profile);

        return $this;
    }

    public function getProfile() {
        $profile = new Profile($this->sourceType, $this->destinationType);

        $this->addPropertyProfiles($profile);

        return $profile;
    }
}