<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\SimpleWeb\ActionResults\ActionResult;
    use Zaggoware\SimpleWeb\Routing\RouteCollection;

    class DefaultControllerFactory implements IControllerFactory {
        public function __construct() {
        }

        /**
         * @param string $controllerName
         * @param string|null $areaName
         * @return IController|null
         * @throws \RuntimeException
         */
        public function createController($controllerName, $areaName = null) {
            if(strpos($controllerName, "\\") === false) {
                $config = Site::getConfig();
                $namespace = $config->getControllerNamespace();

                if (!empty($areaName)) {
                    $namespace =  $config->getAreasControllerNamespace($areaName);
                }

                $controllerName = $namespace . "\\" . ucfirst($controllerName) . "Controller";
            }

            if(!class_exists($controllerName)) {
                throw new \RuntimeException("Controller '$controllerName' could not be found.");
            }

            return new $controllerName();
        }

        /**
         * @param IController $controller
         * @param string $actionName
         * @return ActionResult|mixed|null
         * @throws ArgumentNullException
         * @throws \RuntimeException
         */
        public function invokeAction(IController $controller, $actionName) {
            if(empty($controller)) {
                throw new ArgumentNullException("controller");
            }

            if(empty($actionName)) {
                throw new ArgumentNullException("action");
            }

            $reflection = new \ReflectionClass($controller);

            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $actionName .= "_post";
            }

            if(!$reflection->hasMethod($actionName) || !$reflection->getMethod($actionName)->isPublic()
                || ($_SERVER["REQUEST_METHOD"] !== "POST" && strpos($actionName, "_post") !== false)) {

                // HACK: TODO: beautify with Route mappings
                if ($reflection->hasMethod("__call")) {
                    $action = $reflection->getMethod("__call");

                    return $action->invokeArgs($controller, array($actionName, $_REQUEST));
                }

                throw new \RuntimeException("Action '$actionName' could not be found in controller '". get_class($controller)."'.");
            }

            $action = $reflection->getMethod($actionName);

            $modelFactory = $this->createModelFactory();
            $models = $modelFactory->buildModels($action);

            return $action->invokeArgs($controller, $models);
        }

        /**
         * @param RouteCollection $routeCollection
         * @return IController|null
         * @throws \RuntimeException
         */
        public function resolveController(RouteCollection $routeCollection) {
            $requiredStrings = $routeCollection->getRequiredStrings();
            $dataTokens = $routeCollection->getDataTokens();

            if(!$requiredStrings->containsKey("controller") || empty($requiredStrings["controller"])) {
                throw new \RuntimeException("Missing required route string: controller.");
            }

            $controllerName = ucfirst($requiredStrings["controller"]);
            $areaName = null;
            $dataTokens->tryGetValue("area", $areaName);

            return $this->createController($controllerName, $areaName);
        }

        /**
         * @param IController $controller
         * @param RouteCollection $routeCollection
         * @return ActionResult|mixed|null
         * @throws ArgumentNullException
         * @throws \RuntimeException
         */
        public function resolveAndInvokeControllerAction(IController $controller, RouteCollection $routeCollection) {
            $requiredStrings = $routeCollection->getRequiredStrings();

            if(!$requiredStrings->containsKey("action") || empty($requiredStrings["action"])) {
                throw new \RuntimeException("Missing required route string: action.");
            }

            $actionName = lcfirst($requiredStrings["action"]);

            return $this->invokeAction($controller, $actionName);
        }

        protected function createModelFactory() {
            return new DefaultModelFactory();
        }
    }
}

 