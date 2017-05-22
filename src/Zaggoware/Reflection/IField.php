<?php

namespace Zaggoware\Reflection;

interface IField extends IReflect {
    /**
     * @return bool
     */
    function isPartOfProperty();

    /**
     * @param object $instance
     * @return mixed
     */
    function getValue($instance);

    /**
     * @param object $instance
     * @param object $value
     * @return
     */
    function setValue($instance, $value);
} 