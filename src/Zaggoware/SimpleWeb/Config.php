<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Data\DbConfig;
    use Zaggoware\Exceptions\ArgumentNullException;

    class Config {
        public function __construct() {
        }

        /** @var string */
        private $libraryPath = "src/";

        /** @var string */
        private $defaultController = "Home";

        /** @var string */
        private $defaultAction = "Index";

        /** @var bool */
        private $useLowerCaseUrls = true;

        /** @var string */
        private $namespace;

        /** @var string */
        private $controllerNamespace = "~\\Controllers";

        /** @var string[] */
        private $viewLocations = array(
            "~/Views/{controller}",
            "~/Views/Shared"
        );

        private $basePath = "../";

        private $executionPath = "httpdocs";

        private $areasPath = "Areas";

        /** @var string */
        private $areasControllerNamespace = "~\\Areas\\%s\\Controllers";

        /** @var string[] */
        private $partialViewLocations;

        /** @var string */
        private $viewStart = "~/Views/_ViewStart.php";

        /** @var string[] */
        private $viewFileExtensions = array(".phtml", ".php", ".htm", ".html");

        /** @var DbConfig */
        private $dbConfig;

        /**
         * @return string
         */
        public function getLibraryPath() {
            return $this->libraryPath;
        }

        /**
         * @param string $libraryPath
         * @return $this
         */
        public function setLibraryPath($libraryPath) {
            $this->libraryPath = $libraryPath;

            return $this;
        }

        /**
         * @return string
         */
        public function getDefaultAction() {
            return $this->defaultAction;
        }

        /**
         * @param string $action
         * @return $this
         */
        public function setDefaultAction($action) {
            $this->defaultAction = $action;

            return $this;
        }

        /**
         * @return string
         */
        public function getDefaultController() {
            return $this->defaultController;
        }

        /**
         * @param string $controller
         * @return $this
         */
        public function setDefaultController($controller) {
            $this->defaultController = $controller;

            return $this;
        }

        /**
         * @return boolean
         */
        public function useLowerCaseUrls() {
            return $this->useLowerCaseUrls;
        }

        /**
         * @param boolean $flag
         * @return $this
         */
        public function setUseLowerCaseUrls($flag) {
            $this->useLowerCaseUrls = $flag;

            return $this;
        }

        /**
         * @return string
         */
        public function getNamespace() {
            if(empty($this->namespace)) {
                throw new \RuntimeException("Missing required config item: namespace.");
            }

            return $this->namespace;
        }

        /**
         * @param string $namespace
         * @return $this
         */
        public function setNamespace($namespace) {
            $this->namespace = $namespace;

            return $this;
        }

        /**
         * @return string
         */
        public function getControllerNamespace() {
            if(empty($this->controllerNamespace)) {
                throw new \RuntimeException("Missing required config item: controller namespace.");
            }

            $controllerNamespace = $this->controllerNamespace;

            if($controllerNamespace[0] === "~") {
                $controllerNamespace = $this->getNamespace() . substr($controllerNamespace, 1);
            }

            return $controllerNamespace;
        }

        /**
         * @param string $controllerNamespace
         * @throws ArgumentNullException
         * @return $this
         */
        public function setControllerNamespace($controllerNamespace) {
            if(empty($controllerNamespace)) {
                throw new ArgumentNullException('controllerNamespace');
            }

            $this->controllerNamespace = $controllerNamespace;

            return $this;
        }

        /**
         * @return string[]
         */
        public function getViewLocations() {
            return $this->viewLocations;
        }

        /**
         * @param string[] $viewLocations
         * @return $this
         */
        public function setViewLocations($viewLocations) {
            $this->viewLocations = $viewLocations;

            return $this;
        }

        /**
         * @return string[]
         */
        public function getPartialViewLocations() {
            if($this->partialViewLocations === null) {
                return $this->viewLocations;
            }

            return $this->partialViewLocations;
        }

        /**
         * @param string[] $partialViewLocations
         */
        public function setPartialViewLocations($partialViewLocations) {
            $this->partialViewLocations = $partialViewLocations;
        }


        /**
         * @return string
         */
        public function getViewStart() {
            return $this->viewStart;
        }

        /**
         * @param string $viewStart
         */
        public function setViewStart($viewStart) {
            $this->viewStart = $viewStart;
        }

        /**
         * @return string[]
         */
        public function getViewFileExtensions() {
            return $this->viewFileExtensions;
        }

        /**
         * @param string[] $viewFileExtensions
         */
        public function setViewFileExtensions($viewFileExtensions) {
            $this->viewFileExtensions = $viewFileExtensions;
        }

        /**
         * @return DbConfig
         */
        public function getDbConfig() {
            return $this->dbConfig;
        }

        /**
         * @param DbConfig $dbConfig
         * @return $this
         */
        public function setDbConfig($dbConfig) {
            $this->dbConfig = $dbConfig;

            return $this;
        }

        /**
         * @return string
         */
        public function getBasePath() {
            return $this->basePath;
        }

        /**
         * @param string $basePath
         * @return $this
         */
        public function setBasePath($basePath) {
            $this->basePath = $basePath;

            return $this;
        }

        /**
         * @return string
         */
        public function getExecutionPath() {
            return $this->executionPath;
        }

        /**
         * @param string $executionPath
         * @return $this
         */
        public function setExecutionPath($executionPath) {
            $this->executionPath = $executionPath;

            return $this;
        }

        /**
         * @return string
         */
        public function getAreasPath() {
            return $this->areasPath;
        }

        /**
         * @param string $areasPath
         * @return $this
         */
        public function setAreasPath($areasPath) {
            $this->areasPath = $areasPath;

            return $this;
        }

        /**
         * @param string $areaName
         * @return string
        */
        public function getAreasControllerNamespace($areaName) {
            if(empty($this->areasControllerNamespace)) {
                throw new \RuntimeException("Missing required config item: areas controller namespace.");
            }

            $areasControllerNamespace = sprintf($this->areasControllerNamespace, $areaName);

            if($areasControllerNamespace[0] === "~") {
                $areasControllerNamespace = $this->getNamespace() . substr($areasControllerNamespace, 1);
            }

            return $areasControllerNamespace;
        }
    }
}

 