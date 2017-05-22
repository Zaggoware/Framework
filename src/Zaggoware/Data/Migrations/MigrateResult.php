<?php

namespace Zaggoware\Data\Migrations;

class MigrateResult {
    /** @var bool */
    private $success = false;

    /** @var array */
    private $errors = array();

    public function __construct($success, $errors = array()) {
        $this->success = $success;

        if ($success === false) {
            if (!is_array($errors) && is_string($errors)) {
                $errors = array($errors);
            }

            if (empty($errors) || !is_array($errors)) {
                throw new \InvalidArgumentException("Please provide at least one error.");
            }

            $this->errors = $errors;
        }
    }

    /**
     * @return bool
     */
    public function isSucceeded() {
        return $this->success;
    }

    /**
     * @return array|string
     */
    public function getErrors() {
        return $this->errors;
    }
}