<?php

namespace Zaggoware\SimpleWeb\WebPages {

    use Zaggoware\Generic\Dynamic;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Helpers\HtmlHelper;
    use Zaggoware\Helpers\UrlHelper;

    class WebViewPage extends WebPageBase {
        /**
         * @param string $path
         * @param array $viewData
         * @param array $tempData
         * @param Dynamic $viewBag
         */
        public function __construct($path, $viewData = array(), $tempData = array(), $viewBag = null) {
            parent::__construct($path, $viewData, $tempData, $viewBag);

            $this->html = new HtmlHelper();
            $this->url = new UrlHelper();
            $this->model = isset($viewData["model"]) ? $viewData["model"] : null;
        }

        /** @var HtmlHelper */
        public $html;

        /** @var UrlHelper */
        public $url;

        public $model;

        /** @var array */
        public $sections = array();

        /** @var WebViewPage */
        private $childPage;

        /** @var bool */
        private $isTopLevelPage = true;

        /** @var bool */
        private $isLayoutPage = false;

        /** @var callable */
        private $bodyAction;

        /** @var bool */
        private $hasRenderedBody = false;

        /**
         * @param WebViewPage $page
         * @param callable|null $bodyAction
         */
        public function addLayoutPage($page, $bodyAction = null) {
            if(!empty($page)) {
                $page->childPage = $this;
                $page->isLayoutPage = true;
                $this->isTopLevelPage = false;
                $page->bodyAction = $bodyAction;
            }
        }

        /**
         * @return string
         */
        public function renderPage() {
            if($this->body === null) {
                $this->runPage();
            }

            if($this->isTopLevelPage && $this->bodyAction !== null && !$this->hasRenderedBody) {
                throw new \RuntimeException("Method 'renderBody()' must be called at least once.");
            }

            return $this->body;
        }

        /**
         * @return string|null
         * @throws \HttpException
         */
        public function renderBody() {
            if($this->hasRenderedBody) {
                throw new \HttpException("Method 'renderBody()' can only be called once.");
            }

            if(!$this->isTopLevelPage) {
                throw new \RuntimeException("Method 'renderBody()' can only be called from the top level page.");
            }

            $this->hasRenderedBody = true;

            if($this->bodyAction === null) {
                return "";
            }

            return call_user_func($this->bodyAction);
        }

        public function isSectionDefined($name) {
            $sections = $this->sections;

            if($this->isLayoutPage) {
                $sections = $this->childPage->sections;
            }

            return array_key_exists($name, $sections);
        }

        /**
         * @param string $name
         * @param bool $required
         * @param array $args
         * @return string
         * @throws \RuntimeException
         */
        public function renderSection($name, $required = false, $args = array()) {
            if(!$this->isSectionDefined($name)) {
                if($required) {
                    throw new \RuntimeException("Missing required section $name.");
                }
                return "";
            }

            $sections = $this->sections;

            if($this->isLayoutPage) {
                $sections = $this->childPage->sections;
            }

            if(!is_callable($sections[$name])) {
                throw new \RuntimeException("Section '$name' must be callable.");
            }

            if($args === null) {
                $args = array();
            } else if(!is_array($args)) {
                $args = array($args);
            }

            array_unshift($args, $this);
            return call_user_func_array($sections[$name], $args);
        }
    }
}

 