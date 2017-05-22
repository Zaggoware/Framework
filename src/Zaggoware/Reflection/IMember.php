<?php

namespace Zaggoware\Reflection;

interface IMember extends IReflect {
    /**
     * @return IType
     */
    function getClass();

    /**
     * @return string
     */
    function getMemberType();

    /**
     * @return bool
     */
    function isConstructor();

    /**
     * @return bool
     */
    function isProperty();

    /**
     * @return bool
     */
    function isMethod();

    /**
     * @return bool
     */
    function isField();
} 