<?php

namespace Zaggoware\Linq;

use Zaggoware\Compare\IComparer;
use Zaggoware\Exceptions\NullReferenceException;
use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\IDictionary;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Generic\IList;
use Zaggoware\Reflection\Type;

interface ILinq {

    /* ------------------
     * TODO list:
     * -----------------
     * Rename $callable to according names.
     * Implement new methods.
     * Add following methods:
     * - aggregate
     * - average
     * - cast (?)
     * - concat
     * - defaultIfEmpty (?)
     * - distinct (?)
     * - groupBy
     * - groupJoin (?)
     * - intersect (?)
     * - join
     * - selectMany
     * - sequenceEqual
     * - skipWhile
     * - toLookup
     */

    /**
     * @param callable|null $callable
     * @return mixed
     */
    function all($callable);

    /**
     * @param callable|null $callable
     * @return boolean
     */
    function any($callable = null);

    /**
     * @param callable $selector
     * @return mixed
     */
    function average($selector);

    /**
     * @return IEnumerable
     */
    function asEnumerable();

    /**
     * @param mixed $item
     * @return bool
     */
//    function contains($item);

    /**
     * @param callable|null $callable
     * @return int
     */
    function count($callable = null);

    /**
     * @param callable $callable
     * @return ILinq
     */
    function each($callable);

    /**
     * @param int $index
     * @return mixed
     * @throws NullReferenceException
     */
    function elementAt($index);

    /**
     * @param int $index
     * @return mixed|null
     */
    function elementAtOrDefault($index);

    /**
     * @param callable|null $callable
     * @return mixed
     * @throws NullReferenceException
     */
    function first($callable = null);

    /**
     * @param callable|null $callable
     * @param mixed $default
     * @return mixed|null
     */
    function firstOrDefault($callable = null, $default = null);

    /**
     * @param callable|null $callable
     * @return mixed
     * @throws NullReferenceException
     */
    function last($callable = null);

    /**
     * @param callable|null $callable
     * @param mixed $default
     * @return mixed|null
     */
    function lastOrDefault($callable = null, $default = null);

    /**
     * @param callable|null $selector
     * @return int
     */
    function max($selector = null);

    /**
     * @param callable|null $selector
     * @return int
     */
    function min($selector = null);

    /**
     * @param Type $type
     * @return ILinq
     */
    function ofType(Type $type);

    /**
     * @param callable $selector
     * @param IComparer $comparer
     * @return ILinq
     */
    function orderBy($selector, $comparer = null);

    /**
     * @param callable $selector
     * @param IComparer $comparer
     * @return ILinq
     */
    function orderByDescending($selector, $comparer = null);

    /**
     * @return ILinq
     */
    function reverse();

    /**
     * @param callable $selector
     * @return IEnumerable
     */
    function select($selector);

    /**
     * @param callable|null $predicate
     * @return mixed
     * @throws \Exception
     */
    function single($predicate = null);

    /**
     * @param callable|null $predicate
     * @param mixed|null $default
     * @return mixed|null
     */
    function singleOrDefault($predicate = null, $default = null);

    /**
     * @param int $number
     * @return ILinq
     */
    function skip($number);

    /**
     * @param int $number
     * @param callable $predicate
     * @return ILinq
     */
    function skipWhile($number, $predicate);

    /**
     * @param callable $selector
     * @return number
     */
    function sum($selector);

    /**
     * @param int $number
     * @return ILinq
     */
    function take($number);

    /**
     * @param int $number
     * @param callable $predicate
     * @return ILinq
     */
    function takeWhile($number, $predicate);

    /**
     * @return array
     */
    function toArray();

    /**
     * @return \ArrayObject
     */
    function toArrayObject();

    /**
     * @param callable $keySelector
     * @param callable|null $valueSelector
     * @return IDictionary
     */
    function toDictionary($keySelector, $valueSelector = null);

    /**
     * @return IList
     */
    function toList();

    /**
     * @param IEnumerable $second
     * @return ILinq
     */
    function union($second);

    /**
     * @param callable $callable
     * @return ILinq
     */
    function where($callable);

    /**
     * TODO: [remove this line] zip is same as union but with a result selector.
     *
     * @param IEnumerable $second
     * @param callable $resultSelector
     * @return ILinq
     */
    function zip($second, $resultSelector);
}
 