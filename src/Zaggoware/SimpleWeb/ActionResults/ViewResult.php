<?php

namespace Zaggoware\SimpleWeb\ActionResults {

    use Zaggoware\Generic\Dynamic;
    use Zaggoware\SimpleWeb\IController;
    use Zaggoware\SimpleWeb\ViewEngine;
    use Zaggoware\SimpleWeb\ViewEngineResult;

    class ViewResult extends ViewResultBase {
        /**
         * @param string $viewName
         * @param array $viewData
         * @param array $tempData
         * @param Dynamic $viewBag
         * @param ViewEngine $viewEngine
         */
        public function __construct($viewName = null, $viewData = null, $tempData = null, $viewBag = null, $viewEngine = null) {
            parent::__construct($viewName, $viewData, $tempData, $viewBag, $viewEngine);
        }

        /**
         * @param IController $controller
         * @return ViewEngineResult
         * @throws \RuntimeException
         */
        protected function findView(IController $controller) {
            $result = $this->viewEngine->findView($controller, $this->viewName);

            if($result->getView() != null) {
                return $result;
            }

            throw new \RuntimeException("View '{$this->viewName}' not found, search locations:\r\n<br> ". join(",<br>\r\n", $result->getSearchedLocations()));
        }
    }
}

 