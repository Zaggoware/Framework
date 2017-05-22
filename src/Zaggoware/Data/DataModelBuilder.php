<?php

namespace Zaggoware\Data {

    use Zaggoware\Data\MySql\ColumnNameMapper;
    use Zaggoware\Generic\ArrayList;
    use Zaggoware\Generic\Dictionary;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Generic\KeyValuePair;
    use Zaggoware\Reflection\Type;
    use Zaggoware\Reflection\DefaultModelBuilder;

    class DataModelBuilder extends DefaultModelBuilder {
        public function __construct() {
            parent::__construct();
        }

        /** @var ArrayList */
        private $fetchedData;

        private $currentItem;

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
            $this->fetchedData = new ArrayList();
            $this->currentItem = 0;

            $instance = $this->createInstance($type);

            if($instance === null) {
                return null;
            }

            $this->fetchedData->add($rawData);

            $this->populateModel($type, $instance, $rawData);

            return $instance;
        }

        protected function resolveRawDataValue(IDictionary $rawData, $propertyName) {
            /** @var IDictionary $item */
            $item = null;
            $rawData->tryGetValue($propertyName, $item);

            // Look for custom mappings.
            if ($item instanceof IDictionary) {
                $columnName = $propertyName;

                // Mapper purposes
                if ($item instanceof IDictionary && $item->containsKey("column_name")) {
                    $columnName = $item["column_name"];
                }

                return $this->fetchedData[$this->currentItem][$columnName];
            } else if ($item !== null) {
                return $item;
            }

            // When no mapping and no column is found, try to find the correct column name by mapping the column names to a valid property name.
            foreach ($rawData as $pair) {
                /** @var KeyValuePair $pair */
                if (ColumnNameMapper::map($pair->getKey()) === $propertyName) {
                    return $rawData[$pair->getKey()];
                }
            }

            return null;
        }

    }
}
