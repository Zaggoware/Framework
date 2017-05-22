<?php

namespace Zaggoware\SimpleWeb;

use Zaggoware\Exceptions\ArgumentNullException;
use Zaggoware\SimpleWeb\Routing\RouteCollection;

class AreaRegistrationContext {
    /** @var string */
    private $areaName;

    /** @var RouteCollection */
    private $routes;

    /** @var mixed */
    private $state;

    public function __construct($areaName, RouteCollection $routes, $state = null) {
        if ($areaName === null) {
            throw new ArgumentNullException("areaName");
        }

        if ($areaName === "") {
            throw new \InvalidArgumentException("areaName cannot be empty.");
        }

        $this->areaName = $areaName;
        $this->routes = $routes;
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getAreaName() {
        return $this->areaName;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * @return mixed
     */
    public function getState() {
        return $this->state;
    }

    public function mapRoute($url, $controller, $action) {
        $route = $this->routes->mapRoute($url, $controller, $action, $this->areaName);

        return $route;
    }
}