<?php

namespace Zaggoware\Linq;

use Zaggoware\Compare\IComparer;

interface IOrderedQueryable
{
    function thenBy($keySelector, IComparer $comparer = null);

    function thenByDescending($keySelector, IComparer $comparer = null);
}