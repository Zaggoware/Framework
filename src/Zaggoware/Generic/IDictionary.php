<?php

namespace Zaggoware\Generic;

interface IDictionary extends IEnumerable, \ArrayAccess {
    /**
     * Adds an element with the provided key and value to the dictionary.
     *
     * @param mixed $key
     * @param mixed $value
     */
    function add($key, $value);

    /**
     * Clears the dictionary.
     */
    function clear();

    /**
     * Determines whether the dictionary contains an element with the specified key.
     *
     * @param mixed $key
     * @return bool
     */
    function containsKey($key);

    /**
     * Determines whether the dictionary contains an element with the specified value.
     *
     * @param mixed $value
     * @return bool
     */
    function containsValue($value);

    /**
     * Counts the elements and returns the result.
     *
     * @param callable|null $predicate
     * @return int
     */
    function count($predicate = null);

    /**
     * Gets a collection containing the keys of the dictionary.
     *
     * @return ArrayList
     */
    function getKeys();

    /**
     * Gets a collection containing the values of the dictionary.
     *
     * @return ArrayList
     */
    function getValues();

    /**
     * Removes the element with the specified key from the dictionary.
     *
     * @param mixed $key
     */
    function remove($key);

    /**
     * Gets the value associated with the specified key.
     * Returns true if the dictionary contains an element with the specified key.
     * Otherwise, false.
     *
     * @param mixed $key
     * @param mixed $value 'out'
     * @return bool
     */
    function tryGetValue($key, &$value);

    /**
     * Converts the dictionary to a built-in array object.
     *
     * @return mixed
     */
    function toArray();
}