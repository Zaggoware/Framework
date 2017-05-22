<?php

namespace Zaggoware\SimpleWeb\WebPages {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\Generic\Dynamic;
    use Zaggoware\Helpers\PathHelper;

    abstract class WebPageBase {
        /**
         * @param $path
         * @param array $viewData
         * @param array $tempData
         * @param Dynamic $viewBag
         */
        public function __construct($path, $viewData = array(), $tempData = array(), $viewBag = null) {
            $this->viewData = !empty($viewData) ? $viewData : array();
            $this->tempData = !empty($tempData) ? $tempData : array();
            $this->viewBag = !empty($viewBag) ? $viewBag : new Dynamic();
            $this->path = $path;
        }

        /** @var array */
        public $viewData;

        /** @var array */
        public $tempData;

        /** @var Dynamic */
        public $viewBag;

        /** @var string */
        protected $path;

        /** @var string|null */
        protected $body;


        public function runPage() {
            $old_contents = ob_get_contents();
            ob_clean();

            require $this->path;
            $this->body = ob_get_contents();
            ob_clean();

            // Restore old buffer
            print $old_contents;
        }

        /**
         * @return string
         */
        public abstract function renderPage();

        /**
         * @param string $path
         */
        public function setPath($path) {
            $this->path = $path;
        }

        /**
         * @return string
         */
        public function getPath() {
            return $this->path;
        }
    }
}

 