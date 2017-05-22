<?php

namespace Zaggoware\Reflection {

    use ReflectionProperty;
    use Zaggoware\Data\MySql\ColumnNameMapper;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Helpers\ReflectionHelper;

    class DefaultModelBuilder implements IModelBuilder {
        public function __construct() {
        }

        /**
         * Creates a new instance of the given type and fills its properties
         * that are collected from the raw data.
         *
         * @param Type $type
         * @param IDictionary $rawData
         * @throws \ReflectionException
         * @return mixed
         */
        public function buildModel(Type $type, IDictionary $rawData) {
            $instance = $this->createInstance($type);

            if($instance === null) {
                return null;
            }

            $this->populateModel($type, $instance, $rawData);

            return $instance;
        }

        /**
         * Creates a new instance of the model.
         *
         * @param Type $type
         * @return mixed
         * @throws \ReflectionException
         */
        protected function createInstance(Type $type) {
            if(!$type->isInstantiable()) {
                throw new \ReflectionException("Could not instantiate '$type'.");
            }

            $constructor = $type->getConstructor();
            $constructorParams = array();

            if($constructor !== null) {
                $params = $constructor->getParameters();
                $canInvoke = true;

                // TODO: There is a bug with the constructor parameters. They are never optional and the default value cannot be retrieved..

                foreach($params as $param) {
                    $constructorParams[] = null;
                }

                if (!$canInvoke) {
                    throw new \ReflectionException("Could not instantiate '$type'. Constructor has required parameters.");
                }
            }
            
            return $type->createInstance($constructorParams);
        }

        /**
         * Populates the model.
         *
         * @param Type $type
         * @param mixed $instance
         * @param IDictionary $rawData
         */
        protected function populateModel(Type $type, $instance, IDictionary $rawData) {
            $fields = $type->getFields(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

            foreach ($fields as $field) {
                $fieldName = $field->getName();
                $value = $this->resolveRawDataValue($rawData, $fieldName);
                $this->setPropertyValue($type, $instance, $field, $value);
            }
        }

        protected function resolveRawDataValue(IDictionary $rawData, $propertyName) {
            if ($rawData->containsKey($propertyName)) {
                return $rawData[$propertyName];
            }

            return null;
        }

        /**
         * Tries to set the value of a specific property.
         *
         * @param Type $type
         * @param mixed $instance
         * @param FieldInfo $property
         * @param mixed $value
         * @internal param IDictionary $requestData
         */
        protected function setPropertyValue(Type $type, $instance, FieldInfo $property, $value) {
            $propertyName = $property->getName();

            if ($property->isPublic()) {
                $property->setValue($instance, $value);
            } else {
                $setter = "set". ucfirst($propertyName);
                $setter = $type->getMethod($setter);

                if ($setter === null) {
                    return;
                }

                $params = $setter->getParameters();
                $requiredParamsCount = 0;

                foreach ($params as $param) {
                    if (!$param->isOptional()) {
                        $requiredParamsCount++;
                    }
                }

                if ($setter->isPublic() && $requiredParamsCount === 1) {
                    $value = $value === null ? array(null) : $value;
                    $setter->invoke($instance, $value);
                }
            }
        }
    }
}

 