<?php

namespace Zaggoware\Linq;

use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Exceptions\NullReferenceException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\Dictionary;
use Zaggoware\Generic\IDictionary;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Generic\IList;
use Zaggoware\Reflection\Type;

trait EnumerableTrait {

    /** @var array */
    protected $collection = array();

    /**
     * @param callable|null $callable
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function any($callable = null) {
        if(!is_callable($callable)) {
            return count($this->collection) > 0;
        }

        foreach($this->collection as $item) {
            $result = $callable($item);

            if(!is_bool($result)) {
                throw new \RuntimeException("Callable from method 'any' must return a boolean.");
            }

            if($result === true) {
                return true;
            }
        }

        return false;
    }

    public function average($selector) {
        if (!is_callable($selector)) {
            throw new InvalidArgumentTypeException("selector", $selector, "callable");
        }

        $total = 0;
        $count = 0;
        foreach ($this->collection as $key => $item) {
            $value = $selector($item);

            if ($value === null) {
                continue;
            }

            if (!is_numeric($value)) {
                throw new \LogicException("The returned type at '$key' is not numeric.");
            }

            $total += $value;
            $count++;
        }

        return $total / ($count > 0 ? $count : 1);
    }

    /**
     * @param callable|null $callable
     * @return int
     */
    public function count($callable = null) {
        if(!is_callable($callable)) {
            return count($this->collection);
        }

        $count = 0;
        foreach($this->collection as $item) {
            $result = $callable($item);

            if($result === true) {
                $count++;
            }
        }

        return $count;
    }

    public function distinct() {
        $distinctArray = array();
        foreach ($this->collection as $item) {
            if (!in_array($item, $distinctArray)) {
                array_push($distinctArray, $item);
            }
        }

        return Enumerable::from($distinctArray);
    }

    /**
     * @param callable $callable
     * @return ILinq
     * @throws \InvalidArgumentException
     */
    public function each($callable) {
        if(!is_callable($callable)) {
            throw new InvalidArgumentTypeException("callable", $callable, "callable");
        }

        foreach($this->collection as $item) {
            $callable($item);
        }

        return $this;
    }

    /**
     * @param callable|null $callable
     * @return mixed
     * @throws \Exception
     */
    public function first($callable = null) {
        if (count($this->collection) === 0) {
            throw new \Exception("Sequence contains no elements.");
        }

        if (!is_callable($callable)) {
            return $this->collection[0];
        }

        $items = array();
        foreach ($this->collection as $item) {
            $result = $callable($item);

            if ($result === true) {
                $items[] = $item;
                break;
            }
        }

        if (count($items) === 0) {
            throw new \Exception("Sequence contains no elements.");
        }

        return $items[0];
    }

    /**
     * @param callable|null $callable
     * @param mixed $default
     * @return mixed
     */
    public function firstOrDefault($callable = null, $default = null) {
        if(count($this->collection) === 0) {
            return $default;
        }

        if(!is_callable($callable)) {
            return $this->collection[0];
        }

        $items = array();
        foreach($this->collection as $item) {
            $result = $callable($item);

            if($result === true) {
                $items[] = $item;
            }
        }

        if(count($items) == 0) {
            return $default;
        }

        return $items[0];
    }

    /**
     * @param callable|null $callable
     * @return mixed
     */
    public function last($callable = null) {
        if(!is_callable($callable)) {
            return $this->collection[count($this->collection) - 1];
        }

        $items = array();
        foreach($this->collection as $item) {
            $result = $callable($item);

            if($result === true) {
                $items[] = $item;
            }
        }

        return $items[count($items) - 1];
    }

    /**
     * @param callable|null $callable
     * @param mixed $default
     * @return mixed
     */
    public function lastOrDefault($callable = null, $default = null) {
        if(count($this->collection) == 0) {
            return $default;
        }

        if(!is_callable($callable)) {
            return $this->collection[count($this->collection) - 1];
        }

        $items = array();
        foreach($this->collection as $item) {
            $result = $callable($item);

            if($result === true) {
                $items[] = $item;
            }
        }

        if(count($items) == 0) {
            return $default;
        }

        return $items[count($items) - 1];
    }

    /**
     * @param callable $callable
     * @return ILinq
     */
    public function orderBy($callable, $comparer = null) {
        $collection = $this->internalOrder($callable);

        // TODO: return IOrderedEnumerable which has 'thenBy' and 'thenByDescending' methods.
        return Enumerable::from($collection);
    }

    /**
     * @param callable $callable
     * @return ILinq
     */
    public function orderByDescending($callable, $comparer = null) {
        $collection = $this->internalOrder($callable);
        $collection = array_reverse($collection);

        // TODO: return IOrderedEnumerable which has 'thenBy' and 'thenByDescending' methods.
        return Enumerable::from($collection);
    }

    /**
     * @param int $number
     * @return ILinq
     * @throws \InvalidArgumentException
     */
    public function skip($number) {
        if(!is_numeric($number)) {
            throw new \InvalidArgumentException("Argument 'number' is not a number");
        }

        $skip = (int)$number;
        if ($skip > count($this->collection)) {
            $skip = count($this->collection);
        }

        $items = array_slice($this->collection, $skip);

        return Enumerable::from($items);
    }

    /**
     * @param int $number
     * @return ILinq
     * @throws \InvalidArgumentException
     */
    public function take($number) {
        if(!is_numeric($number)) {
            throw new \InvalidArgumentException("Argument 'number' is not a number");
        }

        $take = (int)$number;
        if($take > count($this->collection)) {
            $take = count($this->collection);
        }

        $items = array_slice($this->collection, 0, $take);

        return Enumerable::from($items);
    }

    /**
     * @param callable $callable
     * @return ILinq
     * @throws \InvalidArgumentException
     */
    public function where($callable) {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException("Argument 'callable' is not callable");
        }

        $items = array();
        foreach ($this->collection as $item) {
            $result = (bool)$callable($item);

            if ($result === true) {
                $items[] = $item;
            }
        }

        return Enumerable::from($items);
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->collection;
    }

    /**
     * @return string
     */
    public function toString() {
        return "[". join(", ", $this->collection) ."]";
    }

    public function toArrayObject() {
        return new \ArrayObject($this->collection);
    }

    /**
     * @param $callable
     * @return array
     */
    private function internalOrder($callable) {
        if(!is_callable($callable)) {
            throw new \InvalidArgumentException("Argument 'callable' is not callable.");
        }

        if(empty($this->collection)) {
            return array();
        }

        if(count($this->collection) < 2) {
            return $this->collection;
        }

        $collection = $this->collection;
        usort($collection, function($a, $b) use($callable) {
            $valA = $callable($a);
            $valB = $callable($b);

            return CompareHelper::compare($valA, $valB);
        });

        return $collection;
    }

    /**
     * @param callable|null $callable
     * @return mixed
     */
    public function all($callable) {
        if (!is_callable($callable)) {
            throw new InvalidArgumentTypeException("callable", $callable, "callable");
        }

        foreach ($this->collection as $item) {
            if (!$callable($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return IEnumerable
     */
    public function asEnumerable() {
        return Enumerable::from($this->collection);
    }

    /**
     * @param mixed $item
     * @return bool
     */
/*    public function contains($item) {
        // TODO: Implement contains() method.
    }*/

    /**
     * @param int $index
     * @return mixed
     * @throws NullReferenceException
     */
    public function elementAt($index) {
        $count = 0;
        foreach ($this->collection as $item) {
            if ($count == $index) {
                return $item;
            }

            $count++;
        }

        throw new \OutOfRangeException("Index is out of the collection range.");
    }

    /**
     * @param int $index
     * @return mixed|null
     */
    public function elementAtOrDefault($index) {
        try {
            return $this->elementAt($index);
        } catch(\OutOfRangeException $e) {
            return null;
        }
    }

    /**
     * @param callable|null $selector
     * @return int
     */
    public function max($selector = null) {
        if ($selector === null) {
            return 0;
        }

        if (!is_callable($selector)) {
            throw new InvalidArgumentTypeException("selector", $selector, "callable");
        }

        $max = -PHP_INT_MAX;
        foreach ($this->collection as $key => $item) {
            $value = $selector($item);
            if (!is_numeric($value)) {
                throw new \LogicException("The returned type at '$key' is not numeric.");
            }

            if ($value > $max) {
                $max = $value;
            }
        }

        return $max;
    }

    /**
     * @param callable|null $selector
     * @return int
     */
    public function min($selector = null) {
        if ($selector === null) {
            return 0;
        }

        if (!is_callable($selector)) {
            throw new InvalidArgumentTypeException("selector", $selector, "callable");
        }

        $min = PHP_INT_MAX;
        foreach ($this->collection as $key => $item) {
            $value = $selector($item);

            if (!is_numeric($value)) {
                throw new \LogicException("The returned type at '$key' is not numeric.");
            }

            if ($value < $min) {
                $min = $value;
            }
        }

        return $min;
    }

    /**
     * @param Type $type
     * @return ILinq
     */
    public function ofType(Type $type) {
        if (!$this->any()) {
            return new ArrayList();
        }

        $result = new ArrayList();
        foreach ($this->collection as $item) {
            $itemType = new Type($item);
            if ($itemType->getName() !== $type->getName()) {
                continue;
            }

            $result->add($item);
        }

        return $result;
    }

    /**
     * @return ArrayList
     */
    public function reverse() {
        return new ArrayList(array_reverse($this->collection));
    }

    /**
     * @param callable $selector
     * @return IEnumerable
     */
    public function select($selector) {
        if(!is_callable($selector)) {
            return count($this->collection) > 0;
        }

        $result = new ArrayList();
        foreach($this->collection as $item) {
            $result->add($selector($item));
        }

        return $result;
    }

    /**
     * @param callable|null $predicate
     * @return mixed
     * @throws \Exception
     */
    public function single($predicate = null) {
        $item = null;

        if($predicate === null) {
            $item = $this->take(1);
        } else {
            $item = $this->where($predicate);
        }

        if($item === null || $item->count() === 0) {
            return null;
        }

        if($item->count() !== 1) {
            throw new \Exception("The collection contains more than one element.");
        }

        return $item->first();
    }

    /**
     * @param callable|null $predicate
     * @param mixed|null $default
     * @throws \Exception
     * @return mixed|null
     */
    public function singleOrDefault($predicate = null, $default = null) {
        $item = null;

        if($predicate === null) {
            $item = $this->take(1);
        } else {
            $item = $this->where($predicate);
        }

        if($item === null || $item->count() === 0) {
            return $default;
        }

        if($item->count() !== 1) {
            throw new \Exception("The collection contains more than one element.");
        }

        return $item->firstOrDefault(null, $default);
    }

    /**
     * @param int $number
     * @param callable $predicate
     * @return ILinq
     */
    public function skipWhile($number, $predicate) {
        // TODO: Implement skipWhile() method.
        return Enumerable::from($this->collection);
    }

    /**
     * @param callable $selector
     * @return number
     */
    public function sum($selector) {
        $total = 0;

        foreach($this->collection as $key => $item) {
            $val = $selector($item);

            if(!is_numeric($val)) {
                throw new \LogicException("The returned type at '$key' is not numeric.");
            }

            $total += $val;
        }

        return $total;
    }

    /**
     * @param int $number
     * @param callable $predicate
     * @return ILinq
     */
    public function takeWhile($number, $predicate) {
        $collection = array();
        $index = 0;

        foreach ($this->collection as $key => $item) {
            if (!$predicate($item) || $index >= $number) {
               break;
            }

            $collection[] = $item;
            $index++;
        }

        // TODO: Implement takeWhile() method.
        return Enumerable::from($collection);
    }

    /**
     * @param callable $keySelector
     * @param callable|null $valueSelector
     * @return IDictionary
     */
    public function toDictionary($keySelector, $valueSelector = null) {
        if (!is_callable($keySelector)) {
            throw new InvalidArgumentTypeException("keySelector", $keySelector, "callable");
        }

        if ($valueSelector !== null) {
            if (!is_callable($valueSelector)) {
                throw new InvalidArgumentTypeException("valueSelector", $valueSelector, "callable");
            }
        }

        $result = array();
        foreach ($this->collection as $item) {
            $key = $keySelector($item);
            $result[$key] = null;

            if ($valueSelector !== null) {
                $value = $valueSelector($item);
                $result[$key] = $value;
            }
        }

        return new Dictionary($result);
    }

    /**
     * @return IList
     */
    public function toList() {
        return new ArrayList($this->collection);
    }

    /**
     * @param IEnumerable|array $second
     * @param IEnumerable|array $_
     * @return ILinq
     */
    public function union($second, $_ = null) {
        // TODO: Don't modify $this->collection, a new Enumerable object is needed.

        $idx = 1;
        foreach(func_get_args() as $arg) {
            if (!is_array($arg) && !($arg instanceof IEnumerable)) {
                throw new \InvalidArgumentException("Argument $idx is not an iterator.");
            }

            foreach ($arg as $key => $val) {
                if (is_numeric($key)) {
                    $this->collection[] = $val;
                    continue;
                }

                $this->collection[$key] = $val;
            }

            $idx++;
        }

        return $this;
    }

    /**
     * TODO: [remove this line] zip is same as union but with a result selector.
     *
     * @param IEnumerable $second
     * @param callable $resultSelector
     * @return ILinq
     */
    public function zip($second, $resultSelector) {
        // TODO: Implement zip() method.
        return $this;
    }
}