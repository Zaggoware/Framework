<?php

namespace Zaggoware\Exceptions;

class ArgumentNullException extends \Exception {
    /**
     * ArgumentNullException constructor.
     *
     * @param string $argumentName
     */
    public function __construct($argumentName) {
        parent::__construct("Argument '$argumentName' cannot be null.");

        $this->code = 6001;
    }
}
