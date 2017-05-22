<?php

namespace Zaggoware\Linq;

use Zaggoware\Compare\IComparer;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Exceptions\NullReferenceException;
use Zaggoware\Generic\IDictionary;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Generic\IList;
use Zaggoware\Linq\Expressions\Expression;
use Zaggoware\Reflection\Type;

trait Queryable {
    /** @var IQueryable */
    protected $source;

    /** @var Expression */
    protected $expression;

    /** @var Type */
    protected $elementType;

    /** @var IQueryProvider */
    protected $provider;

    private function checkSource() {
        if ($this->source === null) {
            throw new \RuntimeException("Source cannot be null.");
        }
    }

    protected function createExpression($callable, $paramName) {
        if (!is_callable($callable) && !($callable instanceof Expression)) {
            throw new InvalidArgumentTypeException($paramName, $callable, "callable");
        }

        return Expression::fromCallable($callable);
    }

    public function where($predicate) {
        $this->checkSource();
        $expression = $this->createExpression($predicate, "predicate");

        /* TODO: We somehow need to parse the predicate...
                 Maybe with an own API? Instead of own php code within the predicate, we make an API or something...?
                 Example: instead of:
                    function ($item) {
                        return $item->name == 'some name';
                    }

                 We can do:
                    Predicate::prop('name')->equals('some name');

                 Or:
                    Predicate::prop('name')->lambda(function($item) { return $item->name == 'some name'; });
                    //Note: This will NOT be executed within the query. This filter only applies on the query result.
        */

        $callResult = Expression::call(
            null,
            new \ReflectionMethod($this, __METHOD__),
            array(
                $this->source->getExpression(),
                Expression::quote($predicate)
            )
        );

        return $this->source->getProvider()->createQuery($callResult);
    }

    public function ofType(Type $type) {
        $this->checkSource();

        $expressions = array(
            $this->source->getExpression(),
        );

        //TODO: define type ?
        $callResult = Expression::call(
            null,
            new \ReflectionMethod($this, __METHOD__),
            $expressions
        );

        return $this->source->getProvider()->createQuery($callResult);
    }

    public function cast(Type $type) {

    }

    public function select($selector) {
        $this->checkSource();

        $expression = $this->createExpression($selector, "selector");

        $expressions = array(
            $this->source->getExpression(),
            Expression::quote($expression)
        );

        $callResult = Expression::call(
            null,
            new \ReflectionMethod($this, __METHOD__),
            $expressions
        );

        return $this->source->getProvider()->createQuery($callResult);
    }

    public function selectMany() {}

    public function join() {}

    public function groupJoin() {}

    public function orderBy($keySelector, IComparer $comparer = null) {
        $this->checkSource();

        $expression = $this->createExpression($keySelector, "keySelector");

        $expressions = array(
            $this->source->getExpression(),
            Expression::quote($expression)
        );

        if ($comparer !== null) {
            $expression[] = Expression::constant($comparer, new Type("IComparer"));
        }

        $callResult = Expression::call(
            null,
            new \ReflectionMethod($this, __METHOD__),
            $expressions
        );

        return $this->source->getProvider()->createQuery($callResult);
    }

    public function orderByDescending($keySelector, IComparer $comparer = null) {

    }

    /**
     * @param callable|null $callable
     * @return mixed
     */
    function all($callable)
    {
        // TODO: Implement all() method.
    }

    /**
     * @param callable|null $callable
     * @return boolean
     */
    function any($callable = null)
    {
        // TODO: Implement any() method.
    }

    /**
     * @param callable $selector
     * @return mixed
     */
    function average($selector)
    {
        // TODO: Implement average() method.
    }

    /**
     * @return IEnumerable
     */
    function asEnumerable()
    {
        // TODO: Implement asEnumerable() method.
    }

    /**
     * @param callable|null $callable
     * @return int
     */
    function count($callable = null)
    {
        // TODO: Implement count() method.
    }

    /**
     * @param callable $callable
     * @return ILinq
     */
    function each($callable)
    {
        // TODO: Implement each() method.
    }

    /**
     * @param int $index
     * @return mixed
     * @throws NullReferenceException
     */
    function elementAt($index)
    {
        // TODO: Implement elementAt() method.
    }

    /**
     * @param int $index
     * @return mixed|null
     */
    function elementAtOrDefault($index)
    {
        // TODO: Implement elementAtOrDefault() method.
    }

    /**
     * @param callable|null $callable
     * @return mixed
     * @throws NullReferenceException
     */
    function first($callable = null)
    {
        // TODO: Implement first() method.
    }

    /**
     * @param callable|null $callable
     * @param mixed $default
     * @return mixed|null
     */
    function firstOrDefault($callable = null, $default = null)
    {
        // TODO: Implement firstOrDefault() method.
    }

    /**
     * @param callable|null $callable
     * @return mixed
     * @throws NullReferenceException
     */
    function last($callable = null)
    {
        // TODO: Implement last() method.
    }

    /**
     * @param callable|null $callable
     * @param mixed $default
     * @return mixed|null
     */
    function lastOrDefault($callable = null, $default = null)
    {
        // TODO: Implement lastOrDefault() method.
    }

    /**
     * @param callable|null $selector
     * @return int
     */
    function max($selector = null)
    {
        // TODO: Implement max() method.
    }

    /**
     * @param callable|null $selector
     * @return int
     */
    function min($selector = null)
    {
        // TODO: Implement min() method.
    }

    /**
     * @return ILinq
     */
    function reverse()
    {
        // TODO: Implement reverse() method.
    }

    /**
     * @param callable|null $predicate
     * @return mixed
     * @throws \Exception
     */
    function single($predicate = null)
    {
        // TODO: Implement single() method.
    }

    /**
     * @param callable|null $predicate
     * @param mixed|null $default
     * @return mixed|null
     */
    function singleOrDefault($predicate = null, $default = null)
    {
        // TODO: Implement singleOrDefault() method.
    }

    /**
     * @param int $number
     * @return ILinq
     */
    function skip($number)
    {
        // TODO: Implement skip() method.
    }

    /**
     * @param int $number
     * @param callable $predicate
     * @return ILinq
     */
    function skipWhile($number, $predicate)
    {
        // TODO: Implement skipWhile() method.
    }

    /**
     * @param callable $selector
     * @return number
     */
    function sum($selector)
    {
        // TODO: Implement sum() method.
    }

    /**
     * @param int $number
     * @return ILinq
     */
    function take($number)
    {
        // TODO: Implement take() method.
    }

    /**
     * @param int $number
     * @param callable $predicate
     * @return ILinq
     */
    function takeWhile($number, $predicate)
    {
        // TODO: Implement takeWhile() method.
    }

    /**
     * @return array
     */
    function toArray()
    {
        // TODO: Implement toArray() method.
    }

    /**
     * @return \ArrayObject
     */
    function toArrayObject()
    {
        // TODO: Implement toArrayObject() method.
    }

    /**
     * @param callable $keySelector
     * @param callable|null $valueSelector
     * @return IDictionary
     */
    function toDictionary($keySelector, $valueSelector = null)
    {
        // TODO: Implement toDictionary() method.
    }

    /**
     * @return IList
     */
    function toList()
    {
        // TODO: Implement toList() method.
    }

    /**
     * @param IEnumerable $second
     * @return ILinq
     */
    function union($second)
    {
        // TODO: Implement union() method.
    }

    /**
     * TODO: [remove this line] zip is same as union but with a result selector.
     *
     * @param IEnumerable $second
     * @param callable $resultSelector
     * @return ILinq
     */
    function zip($second, $resultSelector)
    {
        // TODO: Implement zip() method.
    }
}