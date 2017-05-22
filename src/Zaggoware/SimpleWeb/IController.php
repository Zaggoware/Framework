<?php

namespace Zaggoware\SimpleWeb;

use Zaggoware\SimpleWeb\Routing\RouteCollection;

interface IController {
    function __construct();

    function initialize();
    function cleanup();

    /**
     * @return RouteCollection
     */
    function getRoutes();

    /**
     * @param RouteCollection $routes
     */
    function setRoutes($routes);
}