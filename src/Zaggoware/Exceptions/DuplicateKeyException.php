<?php

namespace Zaggoware\Exceptions;

class DuplicateKeyException extends \Exception {
    /**
     * DuplicateKeyException constructor.
     *
     * @param string|null $message
     */
    public function __construct($message = null) {
        parent::__construct($message);

        $this->code = 6002;
    }
}
