<?php

namespace Zaggoware\Generic;

/**
 * Class stub for ArrayIterator to get closer to the .NET environment.
 *
 * @package Zaggoware\Generic
 */
class Enumerator extends \ArrayIterator {
    /**
     * @param array $array
     * @param int $flags
     */
    public function __construct(array $array, $flags = 0) {
        parent::__construct($array, $flags);
    }
} 