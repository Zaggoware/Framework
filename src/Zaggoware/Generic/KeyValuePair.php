<?php

namespace Zaggoware\Generic {

    class KeyValuePair extends \stdClass {
        public function __construct($key, $value) {
            $this->key = $key;
            $this->value = $value;
        }

        private $key;
        private $value;

        public function getKey() {
            return $this->key;
        }

        public function getValue() {
            return $this->value;
        }

        public function __toString() {
            return sprintf("[%s, %s]", $this->key, $this->value);
        }
    }
}

 