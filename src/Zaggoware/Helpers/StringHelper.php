<?php

namespace Zaggoware\Helpers {

    use Zaggoware\Exceptions\ArgumentNullException;

    class StringHelper {
        /**
         * Checks if the given string starts with the given value.
         *
         * @param string $string
         * @param string $value
         * @param bool $ignoreCase
         * @return bool
         * @throws ArgumentNullException
         */
        public static function startsWith($string, $value, $ignoreCase = false) {
            if($string === null) {
                throw new ArgumentNullException("string");
            }

            if($value === null) {
                throw new ArgumentNullException("value");
            }

            if(strlen($value) > strlen($string)) {
                return false;
            }


            if($ignoreCase) {
                $string = strtolower($string);
                $value = strtolower($value);
            }

            return substr($string, 0, strlen($value)) === $value;
        }

        /**
         * Checks if the given string ends with the given value.
         *
         * @param string $string
         * @param string $value
         * @param bool $ignoreCase
         * @return bool
         * @throws ArgumentNullException
         */
        public static function endsWith($string, $value, $ignoreCase = false) {
            if($string === null) {
                throw new ArgumentNullException("string");
            }

            if($value === null) {
                throw new ArgumentNullException("value");
            }

            if(strlen($value) > strlen($string)) {
                return false;
            }

            if($ignoreCase) {
                $string = strtolower($string);
                $value = strtolower($value);
            }

            return substr($string, -strlen($value)) === $value;
        }

        /**
         * Trims the left side (start) of given string with the given characters (or with whitespaces when no characters are given).
         *
         * @param string $string
         * @param array|string|null $characters
         * @return string
         */
        public static function trimStart($string, $characters = null) {
            return $string;
        }

        /**
         * Trims the right side (end) of given string with the given characters (or with whitespaces when no characters are given).
         * @param string $string
         * @param array|string|null $characters
         * @return string
         */
        public static function trimEnd($string, $characters = null) {
            return $string;
        }

        /**
         * Checks if the given string contains the given value.
         *
         * @param string $string
         * @param string $value
         * @param bool $ignoreCase
         * @throws ArgumentNullException
         * @return bool
         */
        public static function contains($string, $value, $ignoreCase = false) {
            if($string === null) {
                throw new ArgumentNullException("string");
            }

            if($value === null) {
                throw new ArgumentNullException("value");
            }

            if(strlen($value) > strlen($string)) {
                return false;
            }

            if($ignoreCase) {
                $string = strtolower($string);
                $value = strtolower($value);
            }

            return strpos($string, $value) !== false;
        }

        /**
         * Checks if the given string is null, or only contains whitespace characters.
         *
         * @param string $string
         * @return bool
         */
        public static function isNullOrWhitespace($string) {
            return $string === null || (strlen($string) >= 0 && strlen(trim($string)) === 0);
        }

        /**
         * Checks if the given string is null or empty.
         *
         * @param string $string
         * @return bool
         */
        public static function isNullOrEmpty($string) {
            return $string === null || $string === "";
        }
    }
}

 