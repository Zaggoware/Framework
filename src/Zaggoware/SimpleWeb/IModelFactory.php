<?php

namespace Zaggoware\SimpleWeb {

    interface IModelFactory {
        /**
         * @param \ReflectionMethod $method
         * @return array
         */
        function buildModels(\ReflectionMethod $method);
    }
}

 