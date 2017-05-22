<?php

namespace Zaggoware\Generic;

use Traversable;
use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Helpers\JsonHelper;
use Zaggoware\Linq\EnumerableTrait;
use Zaggoware\Reflection\Type;

class ArrayList implements IList, \JsonSerializable {
    use EnumerableTrait;

    /**
     * @param array|null $collection
     */
    public function __construct($collection = array()) {
        if ($collection instanceof IEnumerable) {
            $collection = $collection->toArray();
        }

        if(!is_array($collection)) {
            $this->collection = array($collection);
        } else {
            $this->collection = $collection;
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * @param mixed $item
     */
    public function add($item) {
        $this->collection[] = $item;
    }

    /**
     * @param array|\IteratorAggregate $items
     */
    public function addRange($items) {
        if($items == null || (!is_array($items) && !($items instanceof \IteratorAggregate))) {
            return;
        }

        foreach($items as $item) {
            $this->collection[] = $item;
        }
    }

    public function contains($item) {
        return self::any(function($i) use($item) {
            return $i === $item;
        });
    }

    public function remove($item) {
        if(($index = array_search($item, $this->collection)) !== false) {
            unset($this->collection[$index]);
        }
    }

    public function removeAt($index) {
        if(isset($this->collection[$index])) {
            unset($this->collection[$index]);
        }
    }

    public function insertAt($item, $index) {
        if($index < 0) {
            $index = 0;
        }

        if($index > count($this->collection)) {
            $index = count($this->collection);
        }

        $items = array();

        if ($index > 0) {
            $items = array_slice($this->collection, 0, $index-1);
        }

        $items[] = $item;

        $items = array_merge($items, array_slice($this->collection, $index+1, -1));
        $this->collection = $items;
    }

    public function toString() {
        return join(", ", $this->collection);
    }

    public function indexOf($item) {
        return array_search($item, $this->collection);
    }

    /**
     * Copies the elements of the ICollection to an array, starting at a particular array index.
     *
     * @param array $array
     * @param int $index
     * @throws ArgumentNullException
     * @throws \InvalidArgumentException
     */
    public function copyTo(array &$array, $index) {
        if($array === null) {
            throw new ArgumentNullException("array");
        }

        if(!empty($array)) {
            throw new \InvalidArgumentException("Target array must be empty.");
        }

        if($index === null) {
            $index = 0;
        }

        $keys = array_keys($this->collection);
        $count = count($keys);

        if($index < 0 || $index > $count) {
            throw new \OutOfBoundsException("index");
        }

        foreach ($keys as $key) {
            if(is_numeric($key) && array_key_exists($key, $array)) {
                $array[] = $this->collection[$key];
                continue;
            }

            $array[$key] = $this->collection[$key];
        }
    }

    /**
     * Removes all items from the IList.
     */
    public function clear() {
        $this->collection = array();
    }

    /**
     * Inserts an item to the IList at the specified index.
     *
     * @param int $index
     * @param mixed $item
     */
    public function insert($index, $item) {
        // TODO: Implement insert() method.
    }

    /**
     * Gets a value indicating whether the IList is read-only.
     *
     * @return mixed
     */
    public function isReadOnly() {
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
        if (!is_numeric($offset)) {
            throw new \InvalidArgumentException("Offset must be a number.");
        }

        return array_key_exists($offset, $this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param int $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset) {
        if (!is_numeric($offset)) {
            throw new InvalidArgumentTypeException("offset", $offset, "int");
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
        if (!is_numeric($offset)) {
            throw new \InvalidArgumentException("Offset must be a number.");
        }

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
        if ($this->offsetExists($offset)) {
            unset($this->collection[$offset]);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return $this->getEnumerator();
    }

    /**
     * Returns an enumerator that iterates through a collection.
     *
     * @return Enumerator
     */
    public function getEnumerator() {
        return new Enumerator($this->collection);
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