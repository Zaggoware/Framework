<?php

namespace Zaggoware\Exceptions;

class NullReferenceException extends \Exception {
    /**
     * NullReferenceException constructor.
     *
     * @param string|null $message
     */
    public function __construct($message = null) {
        parent::__construct($message);
    }
}
 