<?php

namespace Zaggoware\SimpleWeb {

    class ViewEngineResult {

        /**
         * @param IView $view
         * @param ViewEngine $viewEngine
         * @param array $searchedLocations
         */
        public function __construct($view = null, $viewEngine = null, $searchedLocations = null) {
            if($view != null) {
                $this->view = $view;
            }

            if($viewEngine != null) {
                $this->viewEngine = $viewEngine;
            }

            if($searchedLocations != null) {
                $this->searchedLocations = $searchedLocations;
            }
        }

        /** @var string[] */
        private $searchedLocations;

        /** @var IView */
        private $view;

        /** @var ViewEngine */
        private $viewEngine;

        /**
         * @return string[]
         */
        public function getSearchedLocations() {
            return $this->searchedLocations;
        }

        /**
         * @return IView
         */
        public function getView() {
            return $this->view;
        }

        /**
         * @return ViewEngine
         */
        public function getViewEngine() {
            return $this->viewEngine;
        }

    }
}

 