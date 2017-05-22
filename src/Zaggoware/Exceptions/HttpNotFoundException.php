<?php

namespace Zaggoware\Exceptions;

class HttpNotFoundException extends \Exception {
    /**
     * HttpNotFoundException constructor.
     *
     * @param int $httpCode
     * @param string|null $message
     * @param \Exception|null $innerException
     */
    public function __construct($httpCode = 0, $message = null, \Exception $innerException = null) {
        parent::__construct($message);

        $this->code = 6004;
    }
}