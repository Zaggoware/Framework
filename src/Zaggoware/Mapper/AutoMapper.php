<?php

namespace Zaggoware\Mapper;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\DuplicateKeyException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\IDictionary;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Reflection\PropertyInfo;
use Zaggoware\Reflection\Type;

class AutoMapper {
    /** @var IDictionary<string, Dictionary<string, Profile>> */
    private static $globalProfiles;

    /**
     * @param object|Type $sourceType
     * @param object|Type $destinationType
     * @param callable|null $optionsAction
     * @throws ArgumentNullException
     * @throws DuplicateKeyException
     */
    public static function createProfile($sourceType, $destinationType, callable $optionsAction = null) {
        if ($sourceType === null) {
            throw new ArgumentNullException("sourceType");
        }

        if ($destinationType === null) {
            throw new ArgumentNullException("destinationType");
        }

        $builder = new ProfileBuilder($sourceType, $destinationType);
        if (is_callable($optionsAction)) {
            $optionsAction($builder);
        }
        $profile = $builder->getProfile();

        $sourceTypeName = $profile->getSourceType()->getName();
        $destinationTypeName = $profile->getDestinationType()->getName();

        $profiles = self::getGlobalProfiles();
        if (!$profiles->containsKey($sourceTypeName)) {
            $profiles->add($sourceTypeName, new Dictionary());
        }

        $profiles = $profiles[$sourceTypeName];
        if ($profiles->containsKey($destinationTypeName)) {
            throw new DuplicateKeyException("There is already an existing mapping profile for destination type '{$destinationTypeName}'.");
        } else {
            $profiles->add($destinationTypeName, $profile);
        }
    }

    /**
     * @param object $source
     * @param object|Type $destinationType
     * @return mixed
     * @throws ArgumentNullException
     * @throws \Exception
     */
    public static function map($source, $destinationType) {
        if ($source === null) {
            throw new ArgumentNullException("source");
        }
        if ($destinationType === null) {
            throw new ArgumentNullException("destinationType");
        }

        $sourceType = new Type($source);

        if ($destinationType instanceof Type) {
            $destination = $destinationType->createInstance();
        } else {
            $destination = $destinationType;
            $destinationType = new Type($destinationType);
        }

        $sourceTypeName = $sourceType->getName();
        $destinationTypeName = $destinationType->getName();

        $profiles = self::getGlobalProfiles();
        if (!$profiles->containsKey($sourceTypeName)) {
            throw new \InvalidArgumentException("No mapping profile found for source type '{$sourceTypeName}'.");
        }

        $profiles = $profiles[$sourceTypeName];

        if (!$profiles->containsKey($destinationTypeName)) {
            throw new \InvalidArgumentException("No mapping profile found for destination type '{$destinationTypeName}' on source type '{$sourceTypeName}'.");
        }

        /** @var Profile $profile */
        $profile = $profiles[$destinationTypeName];

        return self::mapInternal($source, $sourceType, $destination, $destinationType, $profile);
    }

    /**
     * @param array $source
     * @param object|Type $singularDestinationType
     * @return array
     * @throws ArgumentNullException
     */
    public static function mapArray(array $source, $singularDestinationType) {
        if ($source === null) {
            throw new ArgumentNullException("source");
        }

        if ($singularDestinationType === null) {
            throw new ArgumentNullException("singularDestinationType");
        }

        if (!($singularDestinationType instanceof Type)) {
            $singularDestinationType = new Type($singularDestinationType);
        }

        $result = array();
        foreach ($source as $item) {
            $result[] = self::map($item, $singularDestinationType);
        }

        return $result;
    }

    /**
     * @param IEnumerable $source
     * @param object|Type $singularDestinationType
     * @return IEnumerable
     * @throws ArgumentNullException
     */
    public static function mapEnumerable(IEnumerable $source, $singularDestinationType) {
        if ($source === null) {
            throw new ArgumentNullException("source");
        }

        if ($singularDestinationType === null) {
            throw new ArgumentNullException("singularDestinationType");
        }

        if (!($singularDestinationType instanceof Type)) {
            $singularDestinationType = new Type($singularDestinationType);
        }

        $result = new ArrayList();
        foreach ($source as $item) {
            $result->add(self::map($item, $singularDestinationType));
        }

        return $result->asEnumerable();
    }

    /**
     * @param object $source
     * @param Type $sourceType
     * @param object $destination
     * @param Type $destinationType
     * @param Profile $profile
     * @return mixed
     * @throws \Exception
     * @throws \ReflectionException
     */
    private static function mapInternal($source, Type $sourceType, $destination, Type $destinationType, Profile $profile) {
        $properties = $sourceType->getProperties();
        foreach ($properties as $property) {
            /** @var PropertyInfo $property */
            if (!$property->hasGetterMethod()) {
                continue;
            }

            $propertyName = $property->getName();
            $propertyValue = $property->getValue($source);
            $propertyValueType = new Type($propertyValue);

            /** @var PropertyInfo $destinationProperty */
            $destinationProperty = $destinationType->getProperty($propertyName);
            if ($destinationProperty === null) {
                continue;
            }

            $destinationSetterMethod = $destinationProperty->getSetterMethod();

            $propertyProfiles = $profile->getPropertyProfiles();
            $globalProfiles = self::getGlobalProfiles();

            $hasPropertyProfile = $propertyProfiles->containsKey($propertyName);
            $hasGlobalProfile = $globalProfiles->containsKey($propertyValueType->getName());
            $hasProfile = $hasPropertyProfile || $hasGlobalProfile;

            if ($hasProfile) {
                if ($hasPropertyProfile) {
                    /** @var PropertyProfile $propertyProfile */
                    $propertyProfile = $propertyProfiles[$propertyName];
                    if ($propertyProfile->isIgnored()) {
                        continue;
                    }

                    if ($propertyProfile->hasValueSetter()) {
                        $destinationPropertyValue = $propertyProfile->invokeValueSetter($propertyValue);
                    } else {
                        $destinationPropertyValue = $propertyProfile->getDestinationType()->createInstance();
                        $destinationPropertyValue = self::mapInternal(
                            $propertyValue,
                            $propertyValueType,
                            $destinationPropertyValue,
                            new Type($destinationPropertyValue),
                            $propertyProfile);
                    }
                } else {
                    /** @var Dictionary<string, Profile> $globalProfile */
                    $globalProfile = $globalProfiles[$propertyValueType->getName()];
                    if ($globalProfile->isIgnored()) {
                        continue;
                    }

                    if($globalProfile->count() === 0) {
                        throw new \Exception("No mapping profile found for property '$propertyName' on source type '$sourceType'.");
                    } else if ($globalProfile->count() > 1) {
                        throw new \Exception("Multiple mapping profiles are specified for property '$propertyName' on source type '$sourceType'. Please provide the correct profile to use.");
                    } else {
                        $destinationPropertyValue = $propertyProfile->getDestinationType()->createInstance();
                        $destinationPropertyValue = self::mapInternal(
                            $propertyValue,
                            $propertyValueType,
                            $destinationPropertyValue,
                            new Type($destinationPropertyValue),
                            $propertyProfile);
                    }
                }

                $destinationSetterMethod->invoke($destination, array($destinationPropertyValue));
            } else {
                if ($propertyValueType->isBuiltInType() || $propertyValueType->isPhpClass()) {
                    $destinationSetterMethod->invoke($destination, $propertyValue === null ? array(null) : $propertyValue);
                    continue;
                }

                throw new \Exception("No mapping profile found for property '$propertyName' on source type '$sourceType'.");
            }
        }

        return $destination;
    }

    /**
     * @return IDictionary
     */
    private static function getGlobalProfiles() {
        if (self::$globalProfiles === null) {
            self::$globalProfiles = new Dictionary();
        }

        return self::$globalProfiles;
    }
}