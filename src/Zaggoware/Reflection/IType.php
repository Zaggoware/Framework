<?php

namespace Zaggoware\Reflection;

use Zaggoware\Generic\ArrayList;

interface IType extends IReflect {
    /**
     * @return mixed
     */
    function isInterface();

    /**
     * @return bool
     */
    function isClass();

    /**
     * @return bool
     */
    function isAbstract();

    /**
     * @return bool
     */
    function isFinal();

    /**
     * @return bool
     */
    function isValueType();

    /**
     * @return bool
     */
    function isNullable();

    /**
     * @param object $object
     * @return bool
     */
    function isInstance($object);

    /**
     * @return bool
     */
    function hasConstructor();

    /**
     * @return IMethod
     */
    function getConstructor();

    /**
     * @param string $methodName
     * @return bool
     */
    function hasMethod($methodName);

    /**
     * @param string $methodName
     * @return IMethod
     */
    function getMethod($methodName);

    /**
     * @return ArrayList<IMethod>
     */
    function getMethods();

    /**
     * @param string $propertyName
     * @return bool
     */
    function hasProperty($propertyName);

    /**
     * @param string $propertyName
     * @return IProperty
     */
    function getProperty($propertyName);

    /**
     * @return ArrayList<IProperty>
     */
    function getProperties();

    /**
     * @param string $fieldName
     * @return bool
     */
    function hasField($fieldName);

    /**
     * @param string $fieldName
     * @return IField
     */
    function getField($fieldName);

    /**
     * @return ArrayList<IField>
     */
    function getFields();

    /**
     * @param string $memberName
     * @return bool
     */
    function hasMember($memberName);

    /**
     * @param string $memberName
     * @return IMember
     */
    function getMember($memberName);

    /**
     * @return ArrayList<IClassMember>
     */
    function getMembers();
} 