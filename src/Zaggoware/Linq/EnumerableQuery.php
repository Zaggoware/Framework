<?php

namespace Zaggoware\Linq;

use Traversable;
use Zaggoware\Compare\IComparer;
use Zaggoware\Exceptions\InvalidArgumentTypeException;
use Zaggoware\Generic\Enumerator;
use Zaggoware\Generic\IEnumerable;
use Zaggoware\Linq\Expressions\Expression;
use Zaggoware\Reflection\Type;

class EnumerableQuery implements IOrderedQueryable, IQueryable, IEnumerable, IQueryProvider {
    use Queryable;

    /** @var Expressions\Expression */
    private $expression;

    /** @var IEnumerable */
    private $enumerable;

    /**
     * @param IEnumerable $enumerable
     * @param Expression $expression
     * @internal param IEnumerable $elementType
     */
    public function __construct($enumerable, $expression) {
        if ($enumerable !== null) {
            if (!($enumerable instanceof IEnumerable)) {
                throw new InvalidArgumentTypeException("enumerable", $enumerable, "Zaggoware\\Generic\\IEnumerable");
            }

            $this->enumerable = $enumerable;
        }

        if ($expression !== null) {
            if (!($expression instanceof Expression)) {
                throw new InvalidArgumentTypeException("expression", $expression, "Zaggoware\\Linq\\Expressions\\Expression");
            }

            $this->expression = $expression;
        } else {
            $this->expression = Expression::constant($this);
        }
    }

    /**
     * @return Expression
     */
    public function getExpression() {
        return $this->expression;
    }

    /**
     * @return Type
     */
    public function getElementType() {
        return $this->elementType;
    }

    /**
     * @return IQueryProvider
     */
    public function getProvider() {
        return $this->provider;
    }

    /**
     * @param Expression $expression
     * @return IQueryable
     */
    public function createQuery(Expression $expression) {
        // TODO: Implement createQuery() method.
    }

    /**
     * @param Expression $expression
     * @return mixed
     */
    public function execute(Expression $expression) {
        // TODO: Implement execute() method.
    }

    public function thenBy($keySelector, IComparer $comparer = null) {

    }

    public function thenByDescending($keySelector, IComparer $comparer = null) {

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
        return $this->enumerable->getEnumerator();
    }
}