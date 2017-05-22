<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Reflection\IModelBuilder;
    use Zaggoware\Reflection\DefaultModelBuilder;
    use Zaggoware\Reflection\Type;

    class DefaultModelFactory implements IModelFactory {

        /**
         * @param \ReflectionMethod $method
         * @throws \ReflectionException
         * @return array
         */
        public function buildModels(\ReflectionMethod $method) {
            $types = array();
            $requestData = Site::getRequestData("get,post");

            foreach($method->getParameters() as $param) {
                $name = $param->getName();
                $class = $param->getClass();
                $type = $class !== null ? new Type($class->getName()) : null;
                $defaultValue = null;
                $hasDefaultValue = false;

                try {
                    $defaultValue = $param->getDefaultValue();
                    $hasDefaultValue = true;
                } catch(\Exception $e) {
                }

                if($type === null) {
                    if(!$hasDefaultValue && !$requestData->containsKey($name)) {
                        throw new \ReflectionException("Missing request parameter value for '$name'.");
                    }

                    $value = null;
                    $requestData->tryGetValue($name, $value);
                    $types[$name] = $value;

                    if($hasDefaultValue) {
                        if(($defaultValueType = $this->getBuiltInType($defaultValue)) !== false) {
                            if(!settype($value, $defaultValueType)) {
                                throw new \ReflectionException("Could not convert parameter type of '$name' to '$defaultValueType'.");
                            }

                            $types[$name] = $value;
                        }
                    }
                } else {
                    $modelBuilder = $this->createModelBuilder();
                    $types[$name] = $modelBuilder->buildModel($type, $requestData);
                }
            }

            return $types;
        }

        /**
         * Creates a new model builder.
         *
         * @return IModelBuilder
         */
        protected function createModelBuilder() {
            return new DefaultModelBuilder();
        }

        /**
         * Gets the built-in type for $value when possible.
         * When $value is not a built-in type, false will be returned.
         *
         * @param $value
         * @return string|bool
         */
        protected function getBuiltInType($value) {
            if(is_integer($value)) {
                return 'integer';
            } else if(is_float($value)) {
                return 'float';
            } else if(is_string($value)) {
                return 'string';
            } else if(is_bool($value)) {
                return 'boolean';
            }

            return false;
        }
    }
}

 