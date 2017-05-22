<?php

namespace Zaggoware\Exceptions;

class NotImplementedException extends \BadMethodCallException {
    /**
     * NotImplementedException constructor.
     *
     * @param string|null $message
     */
    public function __construct($message = null) {
        if(empty($message)) {
            $message = "Not implemented.";
        }

        parent::__construct($message);

        $this->code = 6005;
    }
}
 