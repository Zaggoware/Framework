<?php

namespace Zaggoware\Exceptions;

class ShutdownException extends \Exception {
    /**
     * ShutdownException constructor.
     *
     * @param string $message
     * @param int $type
     * @param string|null $file
     * @param int|null $line
     * @param \Exception|null $previous
     */
    public function __construct($message = "", $type = E_ERROR, $file = null, $line = null, \Exception $previous = null) {
        parent::__construct($message, $type, $previous);

        $this->file = !empty($file) ? $file : __FILE__;
        $this->line = !empty($line) ? $line : __LINE__;

        $this->code = 6000;
    }
}