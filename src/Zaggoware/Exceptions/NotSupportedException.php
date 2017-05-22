<?php

namespace Zaggoware\Exceptions;

class NotSupportedException extends \Exception {
    /**
     * NotSupportedException constructor.
     *
     * @param string|null $message
     */
    public function __construct($message = null) {
        parent::__construct($message);
    }
}
 