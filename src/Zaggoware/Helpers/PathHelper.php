<?php

namespace Zaggoware\Helpers {

    class PathHelper {
        const PATH_SEPARATOR = "/";

        const BASE_PATH_CHAR = "~";

        private static $basePath = "";

        private static $executionPath = "";

        public static function overrideBasePath($basePath) {
            self::$basePath = $basePath;
        }

        public static function overrideExecutionPath($executionPath) {
            self::$executionPath = $executionPath;
        }

        public static function getBasePath() {
            if(!empty(self::$basePath)) {
                return self::$basePath;
            }

            $root = $_SERVER["DOCUMENT_ROOT"];
            $scriptName = $_SERVER["SCRIPT_NAME"];

            if($scriptName[0] !== self::PATH_SEPARATOR && $scriptName[0] !== "\\") {
                $scriptName = self::PATH_SEPARATOR . $scriptName;
            }

            $scriptName = dirname($scriptName);

            return self::normalize($root . $scriptName);
        }

        public static function normalize($path, $scopeToExecutionPath = false) {
            if(empty($path)) {
                return $path;
            }

            if($path[0] === self::BASE_PATH_CHAR) {
                $path = self::getBasePath() . ($scopeToExecutionPath ? self::$executionPath : "") . substr($path, 1);
            }

            $path = str_replace("\\", self::PATH_SEPARATOR, $path);

            while(strpos($path, self::PATH_SEPARATOR . self::PATH_SEPARATOR) !== false) {
                // removes double slashes.
                $path = str_replace(self::PATH_SEPARATOR . self::PATH_SEPARATOR, self::PATH_SEPARATOR, $path);
            }

            if(substr($path, -1) === self::PATH_SEPARATOR) {
                $path = substr($path, 0, -1);
            }

            return $path;
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isRelative($path) {
            if(empty($path)) {
                return true;
            }

            return $path[0] !== self::BASE_PATH_CHAR && !self::isRoot($path);
        }

        /**
         * @param string $path
         * @return bool
         */
        public static function isRoot($path) {
            if(empty($path)) {
                return false;
            }

            $path = self::normalize($path);

            return $path[0] == self::PATH_SEPARATOR;
        }
    }
}

 