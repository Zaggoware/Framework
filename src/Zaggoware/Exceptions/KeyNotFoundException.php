<?php

namespace Zaggoware\Exceptions;

class KeyNotFoundException extends \Exception {
    /**
     * KeyNotFoundException constructor.
     *
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message);
    }
}
