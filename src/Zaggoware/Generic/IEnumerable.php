<?php

namespace Zaggoware\Generic;

use Zaggoware\Linq\ILinq;

/**
 * Interface stub to get closer to the .NET environment.
 *
 * @package Zaggoware\Generic
 */
interface IEnumerable extends ILinq, \IteratorAggregate {
    /**
     * Returns an enumerator that iterates through a collection.
     *
     * @return Enumerator
     */
    function getEnumerator();
}

 