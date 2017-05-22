<?php

namespace Zaggoware\Exceptions;

use Zaggoware\Reflection\Type;

class InvalidArgumentTypeException extends \InvalidArgumentException {
    /**
     * @param string $argumentName
     * @param mixed $argument
     * @param Type|string|object $expectedTypes
     */
    public function __construct($argumentName, $argument, $expectedTypes) {
        $argumentType = new Type($argument);

        if (!is_array($expectedTypes)) {
            $expectedTypes = array($expectedTypes);
        }

        foreach ($expectedTypes as &$type) {
            if (!($type instanceof Type)) {
                $type = new Type($type);
            }
        }

        $expectedTypes = join("', '", $expectedTypes);

        parent::__construct("Invalid argument type for '$argumentName'. "
            . "Expected '{$expectedTypes}', but got '{$argumentType->getName()}'.");
    }
}
 