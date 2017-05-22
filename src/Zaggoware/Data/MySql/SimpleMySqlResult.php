<?php

namespace Zaggoware\Data\MySql {

    use Traversable;
    use Zaggoware\Data\Entity\DbResult;
    use Zaggoware\Generic\Enumerator;

    class SimpleMySqlResult extends DbResult {
        /**
         * @param SimpleMySqlConnection $connection
         * @param \mysqli_result $result
         */
        public function __construct(SimpleMySqlConnection $connection, \mysqli_result $result) {
            $this->connection = $connection;
            $this->result = $result;
        }

        /** @var SimpleMySqlConnection */
        private $connection;

        /** @var \mysqli_result */
        private $result;

        public function fetch() {
            return $this->result->fetch_row();
        }

        public function fetchAssoc() {
            return $this->result->fetch_assoc();
        }

        public function fetchAll() {
            return $this->result->fetch_all(MYSQLI_NUM);
        }

        public function fetchAllAssoc() {
            return $this->result->fetch_all(MYSQLI_ASSOC);
        }

        public function fetchTyped($type) {
            $instance = null;
            $class = new \ReflectionClass($type);

            if (is_string($type)) {
                $instance = $class->newInstance();
            } else {
                $instance = $type;
            }

            $fetchedResult = $this->result->fetch_assoc();

            foreach ($fetchedResult as $key => $value) {
                if ($class->hasProperty($key)) {
                    $instance->$key = $value;
                }
            }

            return $instance;
        }

        public function fetchAllTyped($type) {
            $instance = null;
            $class = new \ReflectionClass($type);
            $fetchedResult = $this->fetchAllAssoc();
            $result = array();

            foreach ($fetchedResult as $row) {
                $instance = $class->newInstance();

                foreach ($fetchedResult as $key => $value) {
                    if ($class->hasProperty($key)) {
                        $instance->$key = $value;
                    }
                }

                $result[] = $instance;
            }

            return $result;
        }

        public function getRowCount() {
            return $this->result->num_rows;
        }

        public function getInsertId() {
            return $this->connection->getInsertId();
        }

        /**
         * Retrieve an external iterator
         *
         * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
         * @return Traversable An instance of an object implementing <b>Iterator</b> or
         * <b>Traversable</b>
         * @since 5.0.0
         */
        public function getIterator() {
            return new MySqlEnumerator($this);
        }

        public function resetPointer() {
            // TODO: Implement resetPointer() method.
        }

        /**
         * Returns an enumerator that iterates through a collection.
         *
         * @return Enumerator
         */
        function getEnumerator() {
            // TODO: Implement getEnumerator() method.
        }
    }
}