<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\Generic\ArrayList;
    use Zaggoware\Generic\Dynamic;
    use Zaggoware\Helpers\PathHelper;
    use Zaggoware\Helpers\StringHelper;
    use Zaggoware\SimpleWeb\Routing\RouteCollection;

    class ViewEngine {
        public function __construct(RouteCollection $routes, Dynamic $viewBag) {
            $this->routes = $routes;
            $this->viewBag = $viewBag;
        }

        /** @var RouteCollection */
        private $routes;

        /** @var Dynamic */
        private $viewBag;

        /**
         * @param Controller $controller
         * @param $partialViewName
         * @return ViewEngineResult
         * @throws ArgumentNullException
         */
        public function findPartialView(Controller $controller, $partialViewName) {
            if(empty($controller)) {
                throw new ArgumentNullException("controller");
            }

            if(empty($partialViewName)) {
                throw new ArgumentNullException("partialViewName");
            }

            $searchedLocations = new ArrayList();
            $path = $this->getPath($controller, "partialView", $partialViewName, $searchedLocations);

            if(empty($path)) {
                return new ViewEngineResult(null, $this, $searchedLocations->toArray());
            }

            return new ViewEngineResult($this->createPartialView($controller, $path), $this);
        }

        /**
         * @param Controller $controller
         * @param $viewName
         * @throws ArgumentNullException
         * @return ViewEngineResult
         */
        public function findView(Controller $controller, $viewName) {
            if(empty($controller)) {
                throw new ArgumentNullException("controller");
            }

            if(empty($viewName)) {
                throw new ArgumentNullException("viewName");
            }

            $searchedLocations = new ArrayList();
            $path = $this->getPath($controller, "view", $viewName, $searchedLocations);

            if(empty($path)) {
                return new ViewEngineResult(null, $this, $searchedLocations->toArray());
            }

            return new ViewEngineResult($this->createView($controller, $path), $this);
        }

        /**
         * @param Controller $controller
         * @param string $path
         * @return IView
         */
        protected function createPartialView(Controller $controller, $path) {
            return new View($controller, $path);
        }

        /**
         * @param Controller $controller
         * @param string $path
         * @return IView
         */
        protected function createView(Controller $controller, $path) {
            return new View($controller, $path);
        }

        /**
         * @param Controller $controller
         * @param string $searchFormat
         * @param string $name
         * @param \ArrayObject|ArrayList $searchedLocations
         * @return string
         */
        private function getPath(Controller $controller, $searchFormat, $name, ArrayList $searchedLocations) {
            if(empty($name)) {
                return "";
            }

            if(!PathHelper::isRelative($name)) {
                return $this->getPathFromSpecificName($name, $searchedLocations);
            }

            return $this->getPathFromGeneralName($controller, $searchFormat, $name, $searchedLocations);
        }

        private function getPathFromSpecificName($name, ArrayList $searchedLocations) {
            $path = PathHelper::normalize($name);

            $viewFileExtensions = Site::getConfig()->getViewFileExtensions();
            foreach($viewFileExtensions as $extension) {
                if (strlen($path) < strlen($extension) || !StringHelper::endsWith($path, $extension)) {
                    $path .= $extension;
                }

                if(file_exists($path)) {
                    break;
                }

                $searchedLocations->add($path);
                $path = "";
            }

            return $path;
        }

        private function getPathFromGeneralName(Controller $controller, $searchFormat, $name, ArrayList $searchedLocations) {
            $path = "";
            $config = Site::getConfig();
            $viewLocations = $searchFormat == "partialView"
                ? $config->getPartialViewLocations()
                : $config->getViewLocations();

            $requiredStrings = Site::getRoutes()->getRequiredStrings();
            $dataTokens = Site::getRoutes()->getDataTokens();
            $areaName = null;
            $dataTokens->tryGetValue("area", $areaName);

            if (!empty($areaName)) {
                $areasPath = $config->getAreasPath();

                foreach ($viewLocations as &$location) {
                    if ($location[0] === "~") {
                        $location = PathHelper::getBasePath() . $areasPath . "/". $areaName . substr($location, 1);
                    }
                }
            }

            foreach($viewLocations as $location) {
                $controllerName = ucfirst($requiredStrings["controller"]);
                $location = PathHelper::normalize(str_replace("{controller}", $controllerName, $location));

                $viewFileExtensions = Site::getConfig()->getViewFileExtensions();
                foreach($viewFileExtensions as $extension) {
                    $viewPath = $location ."/". $name;

                    if (strlen($viewPath) < strlen($extension) || !StringHelper::endsWith($viewPath, $extension)) {
                        $viewPath .= $extension;
                    }

                    if (file_exists($viewPath)) {
                        $path = $viewPath;
                        break;
                    }

                    $searchedLocations->add($viewPath);
                }
            }

            return $path;
        }
    }
}

 