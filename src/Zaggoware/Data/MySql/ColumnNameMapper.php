<?php

namespace Zaggoware\Data\MySql;

class ColumnNameMapper {
    public static function map($columnName) {
        $parts = explode("_", $columnName);
        $camelCased = '';

        foreach($parts as $part) {
            if ($camelCased === '') {
                $camelCased = strtolower($part);
            } else {
                $camelCased .= ucfirst($part);
            }
        }

        return $camelCased;
    }
} 