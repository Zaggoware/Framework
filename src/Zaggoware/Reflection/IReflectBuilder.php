<?php

namespace Zaggoware\Reflection;

interface IReflectBuilder {
    /**
     * @param string|null $name
     */
    function __construct($name = null);

    /**
     * @return string
     */
    function getName();

    /**
     * @param string $name
     * @return $this
     */
    function setName($name);

    /**
     * @return Type
     */
    function getType();

    /**
     * @param Type $type
     * @return $this
     */
    function setType(Type $type);

    /**
     * @return string
     */
    function getAccessModifier();

    /**
     * @param string $accessModifier See AccessModifier.
     * @return $this
     */
    function setAccessModifier($accessModifier);
}
