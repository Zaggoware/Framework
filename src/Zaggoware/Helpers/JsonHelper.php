<?php

namespace Zaggoware\Helpers;

use Zaggoware\Generic\IEnumerable;
use Zaggoware\Reflection\PropertyInfo;
use Zaggoware\Reflection\Type;

class JsonHelper {
    public static function makeSerializable($object, $ignoreNullValues = false, $declareType = false) {
        if ($object === null) {
            return null;
        }

        $objArray = array();

        if ($object instanceof \DateTime) {
            return self::makeValueSerializable($object, $ignoreNullValues);
        }

        if (is_array($object) || $object instanceof \IteratorAggregate) {
            foreach ($object as $key => $value) {
                $objArray[$key] = self::makeValueSerializable($value, $ignoreNullValues);
            }

            return $objArray;
        }

        $type = new Type($object);

        if ($declareType && $type->getClass()->getParentClass() !== null) {
            $objArray["\$type"] = $type->getShortName();
        }

        foreach ($type->getProperties() as $property) {
            /** @var PropertyInfo $property */
            
            if (!$property->hasGetterMethod()) {
                continue;
            }

            $key = $property->getName();
            $value = $property->getValue($object);
            if ($value === null && $ignoreNullValues) {
                continue;
            }

            $objArray[$key] = self::makeValueSerializable($value, $ignoreNullValues);
        }

        return $objArray;
    }

    public static function serialize($object, $options = 0, $depth = 512) {
        return $options === 0 || $options === null
            ? json_encode($object)
            : ($depth === 512
                ? json_encode($object, $options)
                : json_encode($object, $options, $depth));
    }

    public static function deserialize($json, $type) {
        $obj = json_decode($json);
        $type = $type instanceof Type ? $type : new Type($type);
        $instance = $type->createInstance();

        foreach ($obj as $key => $val) {
            $property = $type->getProperty($key);

            if ($property === null || !$property->hasSetterMethod()) {
                continue;
            }

            $property->setValue($instance, $val);
        }

        return $instance;
    }

    public static function deserializeArray($json, $type) {
        $arr = json_decode($json);
        if (!is_array($arr)) {
            throw new \Exception("obj is not an array.");
        }

        $type = $type instanceof Type ? $type : new Type($type);

        $instances = array();
        foreach ($arr as $item) {
            $instance = $type->createInstance();

            foreach ($item as $key => $val) {
                $property = $type->getProperty($key);

                if ($property === null || !$property->hasSetterMethod()) {
                    continue;
                }

                $property->setValue($instance, $val);
            }

            $instances[] = $instance;
        }

        return $instances;
    }

    private static function makeValueSerializable($value, $ignoreNullValues) {
        if ($value instanceof \DateTime) {
            return $value->format("Y-m-d\\TH:i:s\\Z");
        }

        if (is_object($value)) {
            $valueType = new Type($value);

            if ($valueType->getNamespace() === Type::NAMESPACE_SEPARATOR) {
                // json_encode knows how to encode standard types.
                return $value;
            }

            return self::makeSerializable($value, $ignoreNullValues);
        }

        return $value;
    }
}