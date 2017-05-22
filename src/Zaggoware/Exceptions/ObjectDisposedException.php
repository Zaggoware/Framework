<?php

namespace Zaggoware\Exceptions;

class ObjectDisposedException extends \RuntimeException {
    /**
     * ObjectDisposedException constructor.
     *
     * @param string $objectName
     * @param string $message
     * @param \Exception|null $innerException
     */
    public function __construct($objectName, $message = "", \Exception $innerException = null) {
        parent::__construct(!empty($message) ? $message : $objectName, -2146232798, $innerException);
    }
}