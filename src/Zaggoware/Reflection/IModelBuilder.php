<?php

namespace Zaggoware\Reflection {

    use Zaggoware\Generic\IDictionary;

    interface IModelBuilder {
        /**
         * Creates a new instance of the given type and fills its properties
         * that are collected from the raw data.
         *
         * @param Type $type
         * @param IDictionary $rawData
         * @throws \ReflectionException
         * @return mixed
         */
        function buildModel(Type $type, IDictionary $rawData);
    }
}

 