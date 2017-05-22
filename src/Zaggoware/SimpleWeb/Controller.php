<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Generic\Dynamic;
    use Zaggoware\Helpers\UrlHelper;
    use Zaggoware\SimpleWeb\ActionResults\RedirectResult;
    use Zaggoware\SimpleWeb\ActionResults\RedirectToActionResult;
    use Zaggoware\SimpleWeb\ActionResults\ViewResult;
    use Zaggoware\SimpleWeb\Routing\RouteCollection;

    abstract class Controller implements IController {
        public function __construct() {
        }

        /** @var RouteCollection */
        protected $routes;

        /** @var Dynamic */
        protected $viewBag;

        /** @var array */
        protected $viewData = array();

        /** @var array */
        protected $tempData = array();

        /** @var ViewEngine */
        protected $viewEngine;

        /** @var UrlHelper */
        protected $url;

        public function initialize() {
            $this->viewBag = new Dynamic();
            $this->tempData = !empty($_SESSION["TempData"]) ? $_SESSION["TempData"] : array();
            $this->viewEngine = new ViewEngine($this->routes, $this->viewBag);
            $this->url = new UrlHelper();
        }

        public function cleanup() {
            $_SESSION["TempData"] = $this->tempData;
        }

        protected function view($model = null, $viewName = null) {
            if($model !== null) {
                $this->viewData["model"] = $model;
            }

            return new ViewResult($viewName, $this->viewData, $this->tempData, $this->viewBag, $this->viewEngine);
        }

        protected function redirect($url) {
            return new RedirectResult($url);
        }

        protected function redirectToAction($actionName, $controllerName = null, $routeValues = null) {
            if (empty($controllerName)) {
                $req = $this->getRoutes()->getRequiredStrings();
                $controllerName = $req["controller"];
            }

            return new RedirectToActionResult($controllerName, $actionName, $routeValues);
        }

        /**
         * @param string $customErrorMessage
         * @return HttpNotFoundResult
         */
        protected function httpNotFound($customErrorMessage = null) {
            header("HTTP/1.0 404 Not Found");
            return $customErrorMessage !== null ? $customErrorMessage : "404 - Not Found";
        }

        /**
         * @param string $customErrorMessage
         * @return HttpForbiddenResult
         */
        protected function httpForbidden($customErrorMessage = null) {
            header('HTTP/1.0 403 Forbidden');
            return $customErrorMessage !== null ? $customErrorMessage : "403 - Forbidden";
        }

        /**
         * @return RouteCollection
         */
        public function getRoutes() {
            return $this->routes;
        }

        /**
         * @param RouteCollection $routes
         */
        public function setRoutes($routes) {
            $this->routes = $routes;
        }

        /**
         * @return Dynamic
         */
        public function getViewBag() {
            return $this->viewBag;
        }

        /**
         * @param Dynamic $viewBag
         */
        public function setViewBag($viewBag) {
            $this->viewBag = $viewBag;
        }

        /**
         * @return ViewEngine
         */
        public function getViewEngine() {
            return $this->viewEngine;
        }

        /**
         * @param ViewEngine $viewEngine
         */
        public function setViewEngine($viewEngine) {
            $this->viewEngine = $viewEngine;
        }
    }
}

 