<?php

namespace Zaggoware\Helpers;

class TypeHelper {
    public static function isString($value) {
        if (!is_string($value)) {
            return false;
        }

        return !self::isBuiltInType($value) && strpos($value, "\\") === false;
    }

    public static function isBuiltInType($type) {
        if (!is_string($type)) {
            $type = gettype($type);
        }

        $type = trim($type, "\\");

        switch(strtolower($type)) {
            case "array":
            case "integer":
            case "int":
            case "number":
            case "boolean":
            case "string":
            case "float":
            case "double":
            case "null":
            case "void":
            case "callable":
                return true;
        }

        return false;
    }

    public static function isValueType($type) {
        if (!is_string($type)) {
            $type = gettype($type);
        } else {
            if (self::isString($type)) {
                return true;
            }
        }

        $type = trim($type, "\\");

        switch(strtolower($type)) {
            case "integer":
            case "int":
            case "number":
            case "boolean":
            case "string":
            case "float":
            case "double":
                return true;
        }

        return false;
    }

    public static function normalizeBuiltInType($value) {
        $value = strtolower($value);

        switch ($value) {
            case "int":
            case "number":
                return "integer";
        }

        return $value;
    }
} 