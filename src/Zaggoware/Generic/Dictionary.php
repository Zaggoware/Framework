<?php

namespace Zaggoware\Generic;

use Traversable;
use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\DuplicateKeyException;
use Zaggoware\Exceptions\KeyNotFoundException;
use Zaggoware\Helpers\JsonHelper;
use Zaggoware\Linq\EnumerableTrait;

class Dictionary implements IDictionary, \JsonSerializable {
    use EnumerableTrait;

    /**
     * @param array|null $data
     */
    public function __construct($data = null) {
        if(is_array($data)) {
            $this->collection = $data;
        } else if($data instanceof IEnumerable) {
            foreach($data as $key=>$value) {
                $this->add($key, $value);
            }
        }
    }

    /** @var bool */
    private $readOnly = false;

    public function makeReadOnly() {
        $this->readOnly = true;
    }

    /**
     * Gets the element with the specified key.
     *
     * @param $key
     * @return mixed
     */
    public function get($key) {
        return $this->collection[$key];
    }

    /**
     * Gets an collection containing the keys of the dictionary.
     *
     * @return ArrayList
     */
    public function getKeys() {
        return new ArrayList(array_keys($this->collection));
    }

    /**
     * Gets an collection containing the values of the dictionary.
     *
     * @return ArrayList
     */
    public function getValues() {
        return new ArrayList(array_values($this->collection));
    }

    /**
     * Sets the element with the specified key.
     *
     * @param mixed $key
     * @param mixed $value
     * @throws \RuntimeException
     */
    public function set($key, $value) {
        if($this->readOnly) {
            throw new \RuntimeException("Dictionary is read-only.");
        }

        $this->collection[$key] = $value;
    }

    /**
     * Adds an element with the provided key and value to the dictionary.
     *
     * @param mixed $key
     * @param mixed $value
     * @throws \RuntimeException
     * @throws \Zaggoware\Exceptions\DuplicateKeyException
     */
    public function add($key, $value) {
        if($this->readOnly) {
            throw new \RuntimeException("Dictionary is read-only.");
        }

        if(array_key_exists($key, $this->collection)) {
            throw new DuplicateKeyException("The key '$key' already exists within the collection.'");
        }

        $this->collection[$key] = $value;
    }

    /**
     * Determines whether the dictionary contains an element with the specified key.
     *
     * @param $key
     * @return bool
     */
    public function containsKey($key) {
        return array_key_exists($key, $this->collection);
    }

    /**
     * Determines whether the dictionary contains an element with the specified value.
     *
     * @param mixed $value
     * @return bool
     */
    public function containsValue($value) {
        return in_array($value, $this->collection);
    }

    /**
     * Clears the dictionary.
     */
    public function clear() {
        if($this->readOnly) {
            throw new \RuntimeException("Dictionary is read-only.");
        }

        $this->collection = array();
    }

    /**
     * Removes the element with the specified key from the dictionary.
     *
     * @param mixed $key
     * @throws \RuntimeException
     */
    public function remove($key) {
        if($this->readOnly) {
            throw new \RuntimeException("Dictionary is read-only.");
        }

        if(array_key_exists($key, $this->collection)) {
            unset($this->collection[$key]);
        }
    }

    /**
     * Gets the value associated with the specified key.
     * Returns true if the dictionary contains an element with the specified key.
     * Otherwise, false.
     *
     * @param mixed $key
     * @param mixed $value 'out'
     * @return bool
     */
    public function tryGetValue($key, &$value) {
        $idx = $this->findEntry($key);

        if($idx !== false) {
            $value = $this->collection[$idx];

            return true;
        }

        $value = null;

        return false;
    }

    /**
     * Returns an iterator that iterates through a collection.
     *
     * @return \Iterator
     */
    public function getEnumerator() {
        $items = array();
        foreach ($this->collection as $key => $value) {
            $items[] = new KeyValuePair($key, $value);
        }
        return new Enumerator($items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return $this->getEnumerator();
    }


    private function internalMakeReadOnly() {
        $this->readOnly = true;
    }

    private function findEntry($key) {
        if($key === null) {
            throw new ArgumentNullException("key");
        }

        if(array_key_exists($key, $this->collection)) {
            return $key;
        }

        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset) {
        return $this->containsKey($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @throws KeyNotFoundException
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset) {
        if(!$this->containsKey($offset)) {
            throw new KeyNotFoundException("The given key was not represent in the dictionary.");
        }

        return $this->collection[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value) {
        $this->collection[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    /**
     * Converts the dictionary to a built-in array object.
     *
     * @return mixed
     */
    public function toArray() {
        return $this->collection;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        return JsonHelper::makeSerializable($this);
    }
}