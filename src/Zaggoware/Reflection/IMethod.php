<?php

namespace Zaggoware\Reflection;

use Zaggoware\Generic\ArrayList;

interface IMethod extends IReflect {
    /**
     * @return bool
     */
    function isGetterMethod();

    /**
     * @return bool
     */
    function isSetterMethod();

    /**
     * @return bool
     */
    function hasParameters();

    /**
     * @return ArrayList
     */
    function getParameters();

    /**
     * @return string
     */
    function getMethodType();

    /**
     * @return bool
     */
    function isAbstract();
}