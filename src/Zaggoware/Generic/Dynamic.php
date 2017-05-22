<?php

namespace Zaggoware\Generic {

    use Zaggoware\Helpers\JsonHelper;
    use Zaggoware\Linq\EnumerableTrait;

    class Dynamic implements IEnumerable, \JsonSerializable {
        use EnumerableTrait;

        /**
         * @param array|null $source
         */
        public function __construct($source = null) {
            if(is_array($source)) {
                $this->collection = $source;
            }
        }

        public static function create($source = null) {
            return new Dynamic($source);
        }

        /**
         * @param string $key
         * @return mixed|null
         */
        public function __get($key) {
            if(!array_key_exists($key, $this->collection)) {
                return null;
            }

            return $this->collection[$key];
        }

        /**
         * @param string $key
         * @param mixed $value
         */
        public function __set($key, $value) {
            $this->collection[$key] = $value;
        }

        /**
         * Returns an iterator that iterates through a collection.
         *
         * @return \Iterator
         */
        public function getEnumerator() {
            return new Enumerator($this->collection);
        }

        /**
         * @return \Traversable
         */
        public function getIterator() {
            return $this->getEnumerator();
        }

        /**
         * Specify data which should be serialized to JSON
         * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
         * @return mixed data which can be serialized by <b>json_encode</b>,
         * which is a value of any type other than a resource.
         * @since 5.4.0
         */
        public function jsonSerialize() {
            return JsonHelper::makeSerializable($this);
        }
    }
}

 