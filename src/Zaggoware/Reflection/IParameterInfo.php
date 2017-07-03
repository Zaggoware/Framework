<?php

namespace Zaggoware\Reflection;

interface IParameterInfo extends IReflect {
    /**
     * @return bool
     */
    function isOptional();

    /**
     * @return mixed
     */
    function getDefaultValue();
} 