<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Reflection\MethodInfo;

    interface IModelFactory {
        /**
         * @param MethodInfo $method
         * @return array
         */
        function buildModels(MethodInfo $method);
    }
}

 