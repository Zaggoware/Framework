<?php

namespace Zaggoware\SimpleWeb\Routing {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\Generic\ArrayList;
    use Zaggoware\Generic\Dictionary;
    use Zaggoware\Generic\ICollection;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Generic\IList;
    use Zaggoware\SimpleWeb\Site;

    class RouteCollection extends Dictionary {

        public function __construct() {
            parent::__construct();

            $this->collection = array("routes" => new ArrayList(), "requiredStrings" => new Dictionary(), "dataTokens" => new Dictionary());
        }

        /** @var string */
        private $url;

        /**
         * @param ArrayList $segments
         * @return RouteCollection
         */
        public static function fromUrlSegments(ArrayList $segments) {
            $config = Site::getConfig();
            $controller = $segments->firstOrDefault();
            $action = $segments->elementAt(1);

            if(empty($controller)) {
                $controller = $config->getDefaultController();
            }

            if(empty($action)) {
                $action = $config->getDefaultAction();
            }

            if($config->useLowerCaseUrls()) {
                $controller = strtolower($controller);
                $action = strtolower($action);
            }

            $rc = new RouteCollection();
            $requiredStrings = $rc->getRequiredStrings();
            $requiredStrings->add("controller", $controller);
            $requiredStrings->add("action", $action);

            return $rc;
        }

        /**
         * @param string $url
         * @param string $controller
         * @param string $action
         * @param string|null $area
         * @return Route
         */
        public function mapRoute($url, $controller = null, $action = null, $area = null) {
            return $this->mapRouteInfo(new Route($url, $controller, $action, $area));
        }

        /**
         * @param string $controller
         * @param string $action
         * @return \Zaggoware\SimpleWeb\Routing\Route
         * @throws ArgumentNullException
         */
        public function mapDynamicRoute($controller, $action) {
            if(empty($controller)) {
                throw new ArgumentNullException("controller");
            }

            if(empty($action)) {
                throw new ArgumentNullException("action");
            }

            return $this->mapRouteInfo(new DynamicRoute($controller, $action));
        }

        /**
         * Format: array("url" => array("controller", "action"))
         *
         * @param array $routes
         * @return Route[]
         * @throws \Exception
         */
        public function mapRoutes(array $routes) {
            $routeInfo = array();

            foreach($routes as $url => $data) {
                $controller = $action = null;
                $dataCount = count($data);

                if($dataCount === 1) {
                    $controller = isset($data["controller"]) ? $data["controller"] : $data[0];
                } else if($dataCount === 2) {
                    $controller = isset($data["controller"]) ? $data["controller"] : $data[0];
                    $action =  isset($data["action"]) ? $data["action"] : $data[1];
                } else if($dataCount > 2) {
                    throw new \Exception("Could not parse route information for '$url'.'");
                }

                if(empty($action)) {
                    $action = Site::getConfig()->getDefaultAction();
                }

                $routeInfo[] = $this->mapRouteInfo(new Route($url, $controller, $action));
            }

            return $routeInfo;
        }

        /**
         * @param Route $route
         * @return Route
         */
        public function mapRouteInfo(Route $route) {
            $this->getRoutes()->add($route);

            return $route;
        }

        public function resolveRoutes() {
            $url = $this->getUrl();
            $config = Site::getConfig();
            $matchingRoute = $this->getMatchingRoute($url);

            $segments = new ArrayList(explode("/", $url));
            $controller = $segments->elementAtOrDefault(0);
            $action = $segments->elementAtOrDefault(1);

            if($matchingRoute !== null) {
                $area = $matchingRoute->getArea();

                if (!empty($area)) {
                    $controller = $segments->elementAtOrDefault(1);
                    $action = $segments->elementAtOrDefault(2);
                }

                if (empty($controller)) {
                    $controller = $matchingRoute->getController();
                }

                if (empty($action)) {
                    $action = $matchingRoute->getAction();
                }
            } else {
                $controller = $segments->firstOrDefault();

                if ($segments->count() > 1) {
                    $action = $segments->elementAt(1);
                } else {
                    $action = null;
                }
            }

            if(empty($controller)) {
                $controller = $config->getDefaultController();
            }

            if(empty($action)) {
                $action = $config->getDefaultAction();
            }

            $this->getRequiredStrings()->add("controller", $controller);
            $this->getRequiredStrings()->add("action", $action);

            if (!empty($area)) {
                $this->getDataTokens()["area"] = $area;
            }
        }

        /**
         * @param string|null $url
         * @return Route|null
         */
        public function getMatchingRoute($url = null) {
            if($url === null) {
                $url = $this->getUrl();
            }

            $routes = $this->getRoutes();
            if(!empty($routes)) {
                foreach($routes as $route) {
                    /** @var Route $route */

                    if($route instanceof DynamicRoute) {
                        return $route;
                    }

                    if($route->matchesUrl($url)) {
                        return $route;
                    }
                }
            }

            return null;
        }

        /**
         * @param string $controller
         * @param string $action
         * @param null $area
         * @return null|Route
         * @throws ArgumentNullException
         */
        public function findMatchingRoute($controller, $action, $area = null) {
            if(empty($controller)) {
                throw new ArgumentNullException("controller");
            }

            if(empty($action)) {
                throw new ArgumentNullException("action");
            }

            foreach($this->getRoutes() as $route) {
                /** @var Route $route */
                if($route->matches($controller, $action, $area)) {
                    return $route;
                }
            }

            return null;
        }

        /**
         * @return string
         */
        public function getUrl() {
            if(!empty($this->url)) {
                return $this->url;
            }

            $pathInfo = !empty($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";
            if($pathInfo[0] === "/") {
                $pathInfo = strlen($pathInfo) > 1 ? substr($pathInfo, 1) : "";
            }

            return ($this->url = $pathInfo);
        }

        /**
         * @return IDictionary
         */
        public function getRequiredStrings() {
            return $this->collection["requiredStrings"];
        }

        /**
         * @return IDictionary
         */
        public function getDataTokens() {
            return $this->collection["dataTokens"];
        }

        /**
         * @return IList
         */
        private function getRoutes() {
            return $this->collection["routes"];
        }
    }
}

 