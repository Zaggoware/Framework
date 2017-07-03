<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Exceptions\ArgumentNullException;
    use Zaggoware\Helpers\StringHelper;
    use Zaggoware\Reflection\MethodInfo;
    use Zaggoware\Reflection\Type;
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

            if (strpos($actionName, "_") !== false) {
                throw new \InvalidArgumentException(
                    "Action name cannot contain underscores, as it is reserved for request methods (suffixed).");
            }

            $controllerType = new Type($controller);

            $actionRequestMethods = array(
                "POST" => "post",
                "PUT" => "put",
                "DELETE" => "delete");
/*
            if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
                $allowedRequestMethods = array();

                foreach ($controllerType->getMethods() as $method) {
                    /** @var MethodInfo $method * /
                    $name = strtolower($method->getName());

                    if (strpos($name, "_") !== false) {
                        list($methodName, $requestMethod) = explode("_", $name);

                    }

                    if ($name === strtolower($actionName) || $name === strtolower($actionName) ."_get") {
                        $allowedRequestMethods[] = "GET";
                    } else {

                    }
                }

                // WHAT TO RETURN?
                exit(join(", ", $allowedRequestMethods));
            }*/

            if (array_key_exists($_SERVER["REQUEST_METHOD"], $actionRequestMethods)) {
                $actionName .= "_". $actionRequestMethods[$_SERVER["REQUEST_METHOD"]];
            }

            if (!$controllerType->hasMethod($actionName) || !$controllerType->getMethod($actionName)->isPublic()
                || ($_SERVER["REQUEST_METHOD"] !== "POST" && strpos($actionName, "_post") !== false)) {

                // HACK: TODO: beautify with Route mappings
                if ($controllerType->hasMethod("__call")) {
                    $action = $controllerType->getMethod("__call");

                    return $action->invoke($controller, array($actionName, $_REQUEST));
                }

                throw new \RuntimeException("Action '$actionName' could not be found in controller '". get_class($controller)."'.");
            }

            $action = $controllerType->getMethod($actionName);

            $modelFactory = $this->createModelFactory();
            $models = $modelFactory->buildModels($action);

            return $action->invoke($controller, $models);
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

 