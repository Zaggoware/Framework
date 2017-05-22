<?php

namespace Zaggoware\Data {

    class DbConfig {

        /**
         * @param string|null $name
         * @param string|null $dataType
         * @param string|null $hostName The host name.
         * @param string|null $userName The user name.
         * @param string|null $password The password .
         * @param string|null $databaseName The database name.
         * @param string|null $portNumber The port number.
         * @param string|null $socket The socket.
         */
        public function __construct($name = null, $dataType = null, $hostName = null, $userName = null, $password = null, $databaseName = null, $portNumber = null, $socket = null) {
            $this->name = $name;
            $this->dataType = $dataType;
            $this->hostName = $hostName;
            $this->userName = $userName;
            $this->password = $password;
            $this->portNumber = $portNumber;
            $this->socket = $socket;
            $this->databaseName = $databaseName;
        }

        /** @var string|null */
        private $name;

        /** @var string|null */
        private $dataType;

        /** @var string|null */
        private $hostName;

        /** @var string|null */
        private $userName;

        /** @var string|null */
        private $password;

        /** @var string|null */
        private $databaseName;

        /** @var string|null */
        private $portNumber;

        /** @var string */
        private $socket;

        /**
         * @return null|string
         */
        public function getName() {
            return $this->name;
        }

        /**
         * @param null|string $name
         */
        public function setName($name) {
            $this->name = $name;
        }

        /**
         * @return null|string
         */
        public function getDataType() {
            return $this->dataType;
        }

        /**
         * @param null|string $dataType
         */
        public function setDataType($dataType) {
            $this->dataType = $dataType;
        }

        /**
         * Gets the host name.
         *
         * @return string
         */
        public function getHostName() {
            return $this->hostName;
        }

        /**
         * Sets the host name.
         *
         * @param string $host
         */
        public function setHostName($host) {
            $this->hostName = $host;
        }

        /**
         * Gets the user name.
         *
         * @return string
         */
        public function getUserName() {
            return $this->userName;
        }

        /**
         * Sets the user name.
         *
         * @param string $userName
         */
        public function setUserName($userName) {
            $this->userName = $userName;
        }

        /**
         * Gets the password.
         *
         * @return string
         */
        public function getPassword() {
            return $this->password;
        }

        /**
         * Sets the password.
         *
         * @param string $password
         */
        public function setPassword($password) {
            $this->password = $password;
        }

        /**
         * Gets the database name.
         *
         * @return string|null
         */
        public function getDatabaseName() {
            return $this->databaseName;
        }

        /**
         * Sets the database name.
         *
         * @param string|string $databaseName
         */
        public function setDatabaseName($databaseName) {
            $this->databaseName = $databaseName;
        }

        /**
         * Gets the port number.
         *
         * @return string
         */
        public function getPortNumber() {
            return $this->portNumber;
        }

        /**
         * Sets the port number.
         *
         * @param string $port
         */
        public function setPortNumber($port) {
            $this->portNumber = $port;
        }

        /**
         * Gets the socket.
         *
         * @return string
         */
        public function getSocket() {
            return $this->socket;
        }

        /**
         * Sets the socket.
         *
         * @param string $socket
         */
        public function setSocket($socket) {
            $this->socket = $socket;
        }

    }
}

 