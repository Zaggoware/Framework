<?php

namespace Zaggoware\Reflection;

interface IProperty extends IReflect {
    /**
     * @return bool
     */
    function hasSetterMethod();

    /**
     * @return IMethod
     */
    function getSetterMethod();

    /**
     * @return bool
     */
    function hasGetterMethod();

    /**
     * @return IMethod
     */
    function getGetterMethod();

    /**
     * @return bool
     */
    function isReadOnly();

    /**
     * @return bool
     */
    function isWriteOnly();

    /**
     * @return IField
     */
    function getField();

    /**
     * @param object $object
     * @return mixed
     */
    function getValue($object);
} 