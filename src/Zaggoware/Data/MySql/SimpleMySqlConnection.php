<?php

namespace Zaggoware\Data\MySql {

    use Zaggoware\Data\DataModelBuilder;
    use Zaggoware\Data\DbConfig;
    use Zaggoware\Generic\ArrayList;
    use Zaggoware\Generic\Dictionary;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Generic\KeyValuePair;
    use Zaggoware\Reflection\Type;

    class SimpleMySqlConnection {
        const READ_ONLY = MYSQLI_TRANS_START_READ_ONLY;
        const READ_WRITE = MYSQLI_TRANS_START_READ_WRITE;
        const CONSISTENT_SNAPSHOT = MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT;

        /**
         * @param DbConfig|null $config
         */
        public function __construct($config = null) {
            $this->mappings = new Dictionary();

            if($config !== null) {
                $this->connect($config);
            }
        }

        /** @var \mysqli */
        private $mysql;

        /** @var IDictionary */
        private $mappings;

        public function connect(DbConfig $config) {
            if($this->mysql === null) {
                $this->mysql = new \mysqli();
            }

            $this->mysql->connect(
                $config->getHostName(),
                $config->getUserName(),
                $config->getPassword(),
                $config->getDatabaseName(),
                $config->getPortNumber(),
                $config->getSocket());
        }

        public function close() {
            $this->mysql->close();
        }

        public function escape($value) {
            return $this->mysql->real_escape_string($value);
        }

        /**
         * @param string $query
         * @param Type|string|null $type
         * @return bool|SimpleMySqlResult|ArrayList|null
         */
        public function query($query, $type = null) {
            $result = $this->mysql->query($query);

            if ($result === true) {
                return true;
            }

            if ($result === false && !empty($this->mysql->error)) {
                throw new \PDOException("Query resulted in an error: ". $this->mysql->error);
            }

            if (!($result instanceof \mysqli_result)) {
                return false;
            }

            $result = new SimpleMySqlResult($this, $result);

            if ($type === null) {
                return $result;
            }

            if (!($type instanceof Type)) {
                $type = new Type($type);
            }

            $modelBuilder = new DataModelBuilder();

            if ($this->mappings->containsKey($type->getName())) {
                $mappings = $this->mappings[$type->getName()]->getValue();

                // todo: mappings ???
            }

            $items = new ArrayList();
            foreach ($result->fetchAllAssoc() as $row) {
                $items->add($modelBuilder->buildModel($type, new Dictionary($row)));
            }

            if ($items->count() === 0) {
                return null;
            }

            return $items;
        }

        /**
         * @param string $queries
         * @return bool|SimpleMySqlResult
         */
        public function queryMultiple($queries) {
            $result = $this->mysql->multi_query($queries);

            if ($result === false && !empty($this->mysql->error)) {
                throw new \PDOException("Query resulted in an error: ". $this->mysql->error);
            }

            if (!($result instanceof \mysqli_result)) {
                return false;
            }

            return new SimpleMySqlResult($this, $result);
        }

        public function getError() {
            return $this->mysql->error;
        }

        public function getErrorNumber() {
            return $this->mysql->errno;
        }

        public function getInsertId() {
            return $this->mysql->insert_id;
        }

        public function getAffectedRows() {
            return $this->mysql->affected_rows;
        }

        public function setAutocommit($mode) {
            return $this->mysql->autocommit($mode);
        }

        public function beginTransaction($flags = 0, $name = null) {
            if ($flags === 0 && $name === null) {
                return $this->mysql->begin_transaction();
            }

            if ($flags !== 0 && $name === null) {
                return $this->mysql->begin_transaction($flags);
            }

            return $this->mysql->begin_transaction($flags, $name);
        }

        /**
         * @return bool
         */
        public function commit() {
            return $this->mysql->commit();
        }

        /**
         * @return bool
         */
        public function rollback() {
            return $this->mysql->rollback();
        }

        /**
         * @param string||Type|object $type
         * @param string $table
         * @param IDictionary|array $mappings
         */
        public function registerMappings($type, $table, $mappings) {
            if (!($type instanceof Type)) {
                $type = new Type($type);
            }

            if (is_array($mappings)) {
                $mappings = new Dictionary($mappings);
            } else if (!($mappings instanceof IDictionary)) {
                throw new \InvalidArgumentException("mappings");
            }

            foreach ($mappings as $key => $value) {
                if (is_array($value)) {
                    $mappings[$key] = new Dictionary($value);
                } else if (!($value instanceof KeyValuePair)) {
                    throw new \InvalidArgumentException("mappings");
                }
            }

            $this->mappings->add($type->getName(), new KeyValuePair($table, $mappings));
        }

        /**
         * @return Dictionary|IDictionary
         */
        public function getMappings() {
            return $this->mappings;
        }
    }
}

 