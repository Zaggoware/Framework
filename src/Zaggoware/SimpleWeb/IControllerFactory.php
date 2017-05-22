<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\SimpleWeb\Routing\RouteCollection;

    interface IControllerFactory {
        /**
         * @param string $controllerName
         * @param string|null $areaName
         * @return IController
         */
        function createController($controllerName, $areaName = null);

        /**
         * @param IController $controller
         * @param string $actionName
         * @return mixed
         */
        function invokeAction(IController $controller, $actionName);

        /**
         * @param RouteCollection $routeCollection
         * @return IController
         */
        function resolveController(RouteCollection $routeCollection);

        /**
         * @param IController $controller
         * @param RouteCollection $routeCollection
         * @return mixed
         */
        function resolveAndInvokeControllerAction(IController $controller, RouteCollection $routeCollection);
    }
}

 