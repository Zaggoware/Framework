<?php

namespace Zaggoware\Helpers {

    use Zaggoware\SimpleWeb\Site;

    class UrlHelper {
        private static $baseUrl;

        public static function getBaseUrl($includeServerInfo = false, $scheme = null) {
            if(!empty(self::$baseUrl)) {
                return self::$baseUrl;
            }

            $scheme = $scheme !== null ? $scheme : self::getScheme();
            $serverName = $_SERVER["SERVER_NAME"];
            $port = $_SERVER["SERVER_PORT"];
            $pathInfo = !empty($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "";
            $requestUri = $_SERVER["REQUEST_URI"];

            if(substr($requestUri, -1) != "/") {
                $requestUri .= "/";
            }

            $path = "/";
            if(empty($pathInfo)) {
                $path = $requestUri;
            } else if ($pathInfo != $requestUri) {
                if (($idx = strpos($requestUri, $pathInfo)) !== false) {
                    $path = substr($requestUri, 0, $idx) . "/";
                }
            }

            return $includeServerInfo
                ? "$scheme://$serverName". ($port !== "80" && $port !== "443" ? ":$port" : "") . $path
                : $path;
        }

        public static function getScheme() {
            $scheme = !empty($_SERVER["REQUEST_SCHEME"]) ? $_SERVER["REQUEST_SCHEME"] : null;

            if(empty($scheme)) {
                if(!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
                    $scheme = "https";
                } else {
                    $scheme = "http";
                }
            }

            return $scheme;
        }

        public static function normalize($url, $trailingSlash = false, $includeServerInfo = true) {
            if(empty($url)) {
                return $url;
            }

            if($url[0] === '~') {
                $substrStart = 1;
                if ($url[1] === "/") {
                    $substrStart = 2;
                }
                $url = self::getBaseUrl($includeServerInfo) . substr($url, $substrStart);
            }

            $url = str_replace("\\", "/", $url);
            $scheme = '';

            if (strpos($url, "http://") === 0) {
                $scheme = "http";
            } else if(strpos($url, "https://") === 0) {
                $scheme = "https";
            } else if (strpos($url, "://") === 0) {
                $scheme = self::getScheme();
            }

            if(!empty($scheme)) {
                $scheme .= "://";
                $url = $scheme . substr($url, strlen($scheme));
            }

            if($trailingSlash && substr($url, -1) !== "/") {
                $url = $url ."/";
            } else if (!$trailingSlash && substr($url, -1) === "/") {
                $url = substr($url, 0, -1);
            }

            $url = preg_replace("#//#", "/", $url);

            return $url;
        }

        public static function action($actionName, $controllerName = null, $routeParams = null, $scheme = null) {
            $config = Site::getConfig();
            $useLowerCaseUrls = $config->useLowerCaseUrls();
            $defaultController = $config->getDefaultController();
            $defaultAction = $config->getDefaultAction();

            if (empty($controllerName)) {
                $controllerName = $defaultController;
            }

            if(empty($actionName)) {
                $actionName = $defaultAction;
            }

            $areaName = array_key_exists("area", ($routeParams !== null ? $routeParams : array())) ? $routeParams["area"] : null;

            if($useLowerCaseUrls) {
                $controllerName = strtolower($controllerName);
                $actionName = strtolower($actionName);
                $defaultController = strtolower($defaultController);
                $defaultAction = strtolower($defaultAction);
                $areaName = $areaName !== null ? strtolower($areaName) : null;
            }

            $routes = Site::getRoutes();

            if (($matchingRoute = $routes->findMatchingRoute($controllerName, $actionName, $areaName)) !== null) {
                // TODO: use route params.
                return self::getBaseUrl($scheme !== null, $scheme) . $matchingRoute->parseUrl($controllerName, $actionName, $areaName);
            }

            if($defaultAction == $actionName) {
                $actionName = "";

                if($defaultController == $controllerName) {
                    $controllerName = "";
                }
            } else {
                $controllerName .= "/";
            }

            if (!empty($areaName)) {
                $areaName .= "/";
                unset($routeParams["area"]);
            }

            $queryString = "";
            if (!empty($routeParams)) {
                foreach ($routeParams as $key=>$val) {
                    if (empty($queryString)) {
                        $queryString = "?";
                    } else {
                        $queryString .= "&";
                    }

                    $queryString .= "$key=$val";
                }
            }

            return self::getBaseUrl($scheme !== null, $scheme) . $areaName . $controllerName . $actionName . $queryString;
        }

        public static function content($url, $addFileTime = true) {
            return self::internalContent($url, $addFileTime, false);
        }

        public static function absContent($url, $addFileTime = true) {
            return self::internalContent($url, $addFileTime, true);
        }

        private static function internalContent($url, $addFileTime, $includeServerInfo) {
            $normalizedUrl = self::normalize($url, false, $includeServerInfo);

            if ($addFileTime) {
                $path = PathHelper::normalize($url, true);

                if (!file_exists($path)) {
                    return $normalizedUrl;
                }

                if (strpos($url, "?") === false) {
                    $normalizedUrl .= "?";
                } else {
                    $normalizedUrl .= "&";
                }

                $normalizedUrl .= filemtime($path);
            }

            return $normalizedUrl;
        }
    }
}

 