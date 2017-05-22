<?php

namespace Zaggoware\Linq;

use Traversable;
use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\Enumerator;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Helpers\JsonHelper;
use Zaggoware\Reflection\Type;

class Enumerable implements IEnumerable, \JsonSerializable {
    use EnumerableTrait;

    /**
     * Enumerable constructor.
     * @param array $collection
     */
    private function __construct(array $collection = null) {
        $this->collection = !empty($collection) ? $collection : array();
    }

    /**
     * @param array|IEnumerable $object
     * @return IEnumerable
     * @throws ArgumentNullException
     * @throws InvalidArgumentTypeException
     */
    public static function from($object) {
        if ($object === null) {
            throw new ArgumentNullException("object");
        }

        if ($object instanceof IEnumerable) {
            return new Enumerable($object);
        }

        if (!is_array($object)) {
            throw new InvalidArgumentTypeException("object", $object, Type::arrayType());
        }

        if (count(array_filter(array_keys($object), "is_string")) > 0) {
            return new Dictionary($object);
        }

        return new ArrayList($object);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator() {
        return new \ArrayIterator($this->collection);
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