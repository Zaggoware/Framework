<?php

namespace Zaggoware\Reflection;

interface IReflect {
    /**
     * @return string
     */
    function getName();

    /**
     * @return string
     */
    function getAccessModifier();

    /**
     * @return bool
     */
    function isPrivate();

    /**
     * @return bool
     */
    function isPublic();

    /**
     * @return bool
     */
    function isProtected();

    /**
     * @return bool
     */
    function isStatic();
} 