<?php

namespace Zaggoware\Helpers {

    use Zaggoware\Exceptions\InvalidArgumentTypeException;

    class ReflectionHelper {
        /**
         * @param string $typeName
         * @param mixed|array $args
         * @return object|null
         */
        public static function createInstance($typeName, $args = array()) {
            if(empty($typeName)) {
                return null;
            }

            if($args === null) {
                $args = array();
            } else if(!is_array($args)) {
                $args = array($args);
            }

            $reflection = new \ReflectionClass($typeName);

            return $reflection->newInstanceArgs($args);
        }

        /**
         * @param object|string $object Class name or instance.
         * @param string $typeName
         * @param bool $checkParentTypes
         * @return bool
         */
        public static function isOfType($object, $typeName, $checkParentTypes = true) {
            if($object === null) {
                return false;
            }

            if(empty($typeName)) {
                return false;
            }

            $reflection = new \ReflectionClass($object);
            $typeName = strtolower($typeName);

            do {
                if(strtolower($reflection->getName()) == $typeName) {
                    return true;
                }

                if(!$checkParentTypes) {
                    return false;
                }
            } while($reflection = $reflection->getParentClass());

            return false;
        }

        /**
         * @param object $class
         * @param string $methodName
         * @param mixed|array $args
         * @throws \ReflectionException
         * @throws \InvalidArgumentException
         * @throws \BadMethodCallException
         * @return mixed
         */
        public static function invokeMethod($class, $methodName, $args = array()) {
            if($class === null || !is_object($class)) {
                throw new \InvalidArgumentException("Argument 'class' must be an instance of an object.");
            }

            if(empty($methodName)) {
                throw new \InvalidArgumentException("Cannot invoke non-provided method.");
            }

            if($args === null) {
                $args = array();
            } else if(!is_array($args)) {
                $args = array($args);
            }

            $reflection = new \ReflectionClass($class);
            if(!$reflection->hasMethod($methodName)) {
                throw new \ReflectionException("Method '$methodName' not found on object '{$reflection->getName()}'.");
            }

            $method = $reflection->getMethod($methodName);

            if(!$method->isPublic()) {
                if(!StringHelper::startsWith($method->getName(), "internal", true)) {
                    throw new \BadMethodCallException("Method '$methodName' is not public.'");
                }

                $method->setAccessible(true);
            }

            if($method->isStatic()) {
                $class = null;
            }

            return $method->invokeArgs($class, $args);
        }

        /**
         * @param string $functionName
         * @param mixed|array $args
         * @return mixed
         * @throws \InvalidArgumentException
         */
        public static function invokeFunction($functionName, $args = array()) {
            if(empty($functionName)) {
                throw new \InvalidArgumentException("Cannot invoke non-provided function.");
            }

            if($args === null) {
                $args = array();
            } else if(!is_array($args)) {
                $args = array($args);
            }

            $reflection = new \ReflectionFunction($functionName);

            return $reflection->invokeArgs($args);
        }

        /**
         * @param callable $function
         * @param array $args
         * @return mixed
         * @throws \InvalidArgumentException
         */
        public static function invokeAnonymousFunction($function, $args = array()) {
            if(!is_callable($function)) {
                throw new \InvalidArgumentException("Cannot invoke non-callable object.");
            }

            if($args === null) {
                $args = array();
            } else if(!is_array($args)) {
                $args = array($args);
            }

            $reflection = new \ReflectionFunction($function);
            return $reflection->invokeArgs($args);
        }

        /**
         * @param object|string $class
         * @param string $method
         * @return string
         * @throws \InvalidArgumentException
         */
        public static function getMethodArgumentsAsString($class, $method) {
            if(!is_string($class) && !is_object($class)) {
                throw new \InvalidArgumentException("Argument 'function' must be a callable function, or the name of the function.");
            }

            if(!is_string($method)) {
                throw new \InvalidArgumentException("Argument 'method' must be a string.");
            }

            $reflection = new \ReflectionMethod($class, $method);

            return self::getFuncArgs($reflection);
        }
        /**
         * @param callable|string $function
         * @return string
         * @throws \InvalidArgumentException
         */
        public static function getFunctionArgumentsAsString($function) {
            if(!is_string($function) && !is_callable($function)) {
                throw new \InvalidArgumentException("Argument 'function' must be a callable function, or the name of the function.");
            }

            $reflection = new \ReflectionFunction($function);

            return self::getFuncArgs($reflection);
        }

        /**
         * @param \ReflectionFunctionAbstract $reflection
         * @return string
         */
        private static function getFuncArgs(\ReflectionFunctionAbstract $reflection) {
            $args = $reflection->getParameters();
            $result = "";

            foreach($args as $arg) {
                if(!empty($result)) {
                    $result .= ", ";
                }

                $argClass = $arg->getClass();
                $argName = $arg->getName();

                if(!empty($argClass)) {
                    $result .= $argClass->getShortName() ." ";
                }

                if($arg->isOptional()) {
                    $result .= "[$". $argName ."]";
                } else {
                    $result .= "$". $arg->getName();
                }
            }

            return $result;
        }

        public static function fieldExists($class, $field) {
            if ($class === null || (!is_object($class) && !is_string($class))) {
                throw new \InvalidArgumentException("Argument 'class' must be an instance of an object, or the name of the class.");
            }

            $reflection = new \ReflectionClass($class);

            return $reflection->hasProperty($field);
        }
    }
}
 