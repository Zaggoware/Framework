<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Helpers\UrlHelper;
    use Zaggoware\SimpleWeb\ActionResults\JsonResult;
    use Zaggoware\SimpleWeb\Routing\RouteCollection;

    abstract class ApiController implements IController {
        public function __construct() {
        }

        /** @var RouteCollection */
        protected $routes;

        /** @var UrlHelper */
        protected $url;

        public function initialize() {
            $this->url = new UrlHelper();
        }

        public function cleanup() {
        }

        /**
         * @param mixed $object
         * @return JsonResult
         */
        protected function json($object) {
            return new JsonResult($object);
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
    }
}

 