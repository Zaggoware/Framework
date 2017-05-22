<?php

namespace Zaggoware\Linq;

use Zaggoware\Generic\IEnumerable;
use Zaggoware\Linq\Expressions\Expression;
use Zaggoware\Reflection\Type;

interface IQueryable extends ILinq, IEnumerable {

    /**
     * @return Expression
     */
    function getExpression();

    /**
     * @return Type
     */
    function getElementType();

    /**
     * @return IQueryProvider
     */
    function getProvider();
} 