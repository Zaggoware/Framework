<?php

namespace Zaggoware\SimpleWeb\ActionResults {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\Generic\Dynamic;
    use Zaggoware\SimpleWeb\IController;
    use Zaggoware\SimpleWeb\IView;
    use Zaggoware\SimpleWeb\ViewEngine;
    use Zaggoware\SimpleWeb\ViewEngineResult;

    abstract class ViewResultBase extends ActionResult {
        /**
         * @param string $viewName
         * @param array $viewData
         * @param array $tempData
         * @param Dynamic $viewBag
         * @param ViewEngine $viewEngine
         */
        public function __construct($viewName = null, $viewData = null, $tempData = null, $viewBag = null, $viewEngine = null) {
            $this->viewName = $viewName;
            $this->viewData = $viewData;
            $this->tempData = $tempData;
            $this->viewBag = !empty($viewBag) ? $viewBag : new Dynamic();
            $this->viewEngine = $viewEngine;
        }

        /** @var string */
        protected $viewName;

        /** @var array */
        protected $viewData;

        /** @var array */
        protected $tempData;

        /** @var Dynamic */
        protected $viewBag;

        /** @var IView */
        protected $view;

        /** @var ViewEngine */
        protected $viewEngine;

        /**
         * @param IController $controller
         * @return string
         * @throws ArgumentNullException
         */
        public function executeResult(IController $controller) {
            if(empty($this->viewName)) {
                $requiredStrings = $controller->getRoutes()->getRequiredStrings();
                $this->viewName = $requiredStrings["action"];
            }

            $this->viewName = ucfirst($this->viewName);

            /** @var ViewEngineResult $result */
            $result = null;

            if($this->view === null) {
                $result = $this->findView($controller);
                $this->view = $result->getView();
            }

            return $this->view->render($this->viewData, $this->tempData, $this->viewBag);
        }

        protected  abstract function findView(IController $controller);

        /**
         * @return mixed
         */
        public function getModel() {
            if(isset($this->viewData["model"])) {
                return $this->viewData["model"];
            }
            return null;
        }

        /**
         * @return IView
         */
        public function getView() {
            return $this->view;
        }

        /**
         * @return Dynamic
         */
        public function getViewBag() {
            return $this->viewBag;
        }

        /**
         * @return mixed
         */
        public function getViewName() {
            return $this->viewName;
        }
    }
}

 