<?php

namespace Zaggoware\Exceptions;

class FileNotFoundException extends \Exception {
    /**
     * FileNotFoundException constructor.
     *
     * @param string $path
     */
    public function __construct($path) {
        parent::__construct("File '$path' could not be found.'");

        $this->code = 6003;
    }
}