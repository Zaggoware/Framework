<?php

namespace Zaggoware\SimpleWeb\WebPages {

    class StartPage extends WebPageBase {
        /** @var WebPageBase */
        protected $childPage;

        /** @var string */
        protected $layout;

        public function renderPage() {
            return $this->childPage->renderPage($this->path);
        }

        /**
         * @param WebPageBase $childPage
         */
        public function setChildPage($childPage) {
            $this->childPage = $childPage;
        }

        /**
         * @return WebPageBase
         */
        public function getChildPage() {
            return $this->childPage;
        }

        /**
         * @param string $layout
         */
        public function setLayout($layout) {
            $this->layout = $layout;
        }

        /**
         * @return string
         */
        public function getLayoutPath() {
            return $this->layout;
        }
    }
}

 