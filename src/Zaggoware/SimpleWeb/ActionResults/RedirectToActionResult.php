<?php

namespace Zaggoware\SimpleWeb\ActionResults {

    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Helpers\UrlHelper;
    use Zaggoware\SimpleWeb\IController;

    class RedirectToActionResult extends ActionResult {

        /**
         * @param string $controllerName
         * @param string $actionName
         * @param IDictionary|array $routeValues
         */
        public function __construct($controllerName, $actionName, $routeValues) {
            $this->controllerName = $controllerName;
            $this->actionName = $actionName;
            $this->routeValues = $routeValues;
        }

        /** @var string */
        private $controllerName;

        /** @var string */
        private $actionName;

        /** @var IDictionary|array */
        private $routeValues;

        public function executeResult(IController $controller) {
            header("Location: ". UrlHelper::action($this->actionName, $this->controllerName, $this->routeValues));
        }
    }
}

 