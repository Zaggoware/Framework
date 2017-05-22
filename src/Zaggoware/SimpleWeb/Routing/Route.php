<?php

namespace Zaggoware\SimpleWeb\Routing {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\Generic\ArrayList;
    use Zaggoware\Helpers\UrlHelper;
    use Zaggoware\SimpleWeb\Site;

    class Route {
        /**
         * @param string $url
         * @param string $controller
         * @param string $action
         * @param string|null $area
         * @throws ArgumentNullException
         */
        public function __construct($url, $controller, $action, $area = null) {
            if($url === null) {
                throw new ArgumentNullException("url");
            }

            if($controller === null) {
                throw new ArgumentNullException("controller");
            }

            if($action === null) {
                throw new ArgumentNullException("action");
            }

            $this->url = UrlHelper::normalize($url);
            $this->controller = $controller;
            $this->action = $action;
            $this->area = $area;
        }

        /** @var string */
        protected $url;

        /** @var string */
        protected $controller;

        /** @var string */
        protected $action;

        /** @var string */
        protected $area;

        public function matches($controller, $action, $area = null) {
            return strtolower($this->controller) === strtolower($controller)
                && strtolower($this->action) === strtolower($action)
                && (!empty($area) ? strtolower($this->area) === strtolower($area) : true);
        }

        public function matchesUrl($url, $caseSensitive = false) {
            $defaultController = $this->controller;
            $defaultAction = $this->action;

            $segments = new ArrayList(explode("/", $url));
            if (!empty($this->area)) {
                $controller = $segments->elementAtOrDefault(1);
                $action = $segments->elementAtOrDefault(2);
            } else {
                $controller = $segments->elementAtOrDefault(0);
                $action = $segments->elementAtOrDefault(1);
            }

            //$this->controller = $controller;
            //$this->action = $action;

            $controller = !empty($controller) ? $controller : $defaultController;
            $action = !empty($action) ? $action : $defaultAction;

            if(!$caseSensitive) {
                $url = strtolower($url);
                $this->url = strtolower($this->url);
                $defaultController = strtolower($defaultController);
                $defaultAction = strtolower($defaultAction);
                $controller = strtolower($controller);
                $action = strtolower($action);
            }

            $url = UrlHelper::normalize($url);
            $curUrl = preg_replace("/\\{controller\\}/i", $controller, $this->url);

            if (preg_replace("/\\{action\\}/i", $action, $curUrl) === $url) {
                return true;
            }

            if ($action === $defaultAction && preg_replace("/\\/?\\{action\\}/i", "", $curUrl) === $url) {
                return true;
            }

            if ($controller === $defaultController && $action === $defaultAction) {
                $curUrl = preg_replace("/\\/?\\{controller\\}/i", "", $this->url);
                $curUrl = preg_replace("/\\/?\\{action\\}/i", "", $curUrl);

                if ($curUrl === $url) {
                    return true;
                }
            }

            return false;
        }

        public function parseUrl($controllerName, $actionName, $areaName = null, $caseSensitive = false) {
            $defaultController = $this->controller;
            $defaultAction = $this->action;

            if (!$caseSensitive) {
                $controllerName = strtolower($controllerName);
                $actionName = strtolower($actionName);
                $defaultController = strtolower($defaultController);
                $defaultAction = strtolower($defaultAction);
            }

            if ($defaultController === $controllerName) {
                $controllerName = "";
            }

            if ($defaultAction === $actionName) {
                $actionName = "";
            }

            $url = $this->url;

            if ($areaName !== null) {
                $url = preg_replace("/\\{area\\}/i", $areaName, $url);
            }

            $url = preg_replace("/\\{controller\\}/i", $controllerName, $url);
            $url = preg_replace("/\\{action\\}/i", $actionName, $url);

            return UrlHelper::normalize($url);
        }

        /**
         * @return string
         */
        public function getAction() {
            return $this->action;
        }

        /**
         * @return string
         */
        public function getController() {
            return $this->controller;
        }

        /**
         * @return string
         */
        public function getUrl() {
            return $this->url;
        }

        /**
         * @return string
         */
        public function getArea() {
            return $this->area;
        }
    }
}

 