<?php

namespace Zaggoware\SimpleWeb;

use Zaggoware\Helpers\ReflectionHelper;
use Zaggoware\SimpleWeb\Routing\RouteCollection;

abstract class AreaRegistration {
    private static $areas = array();

    public abstract function getAreaName();

    public static function registerAllAreas($state = null) {
        self::registerAllAreasInternal(Site::getRoutes(), $state);
    }

    private static function registerAllAreasInternal(RouteCollection $routes, $state) {
        $config = Site::getConfig();
        $areasPath = $config->getBasePath() . $config->getAreasPath();
        $namespace = $config->getNamespace() ."\\Areas";
        $areaDirs = array_filter(glob($areasPath ."/*"), 'is_dir');

        foreach ($areaDirs as $dir) {
            $areaName = basename($dir);
            $registrationClass = $namespace ."\\". $areaName ."\\". $areaName ."AreaRegistration";
            /** @var AreaRegistration $areaRegistration */
            $areaRegistration = ReflectionHelper::createInstance($registrationClass);
            $areaRegistration->createContextAndRegister($routes, $state);

            self::$areas[] = $areaName;
        }
    }

    public abstract function registerArea(AreaRegistrationContext $context);

    private function createContextAndRegister(RouteCollection $routes, $state = null) {
        $context = new AreaRegistrationContext($this->getAreaName(), $routes, $state);
        $this->registerArea($context);
    }
}