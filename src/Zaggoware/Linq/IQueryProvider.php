<?php

namespace Zaggoware\Linq;

use Zaggoware\Linq\Expressions\Expression;

interface IQueryProvider {
    /**
     * @param Expression $expression
     * @return IQueryable
     */
    function createQuery(Expression $expression);

    /**
     * @param Expression $expression
     * @return mixed
     */
    function execute(Expression $expression);
}