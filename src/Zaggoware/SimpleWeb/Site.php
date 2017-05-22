<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Exceptions\ShutdownException;
    use Zaggoware\Generic\Dictionary;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Helpers\PathHelper;
    use Zaggoware\SimpleWeb\ActionResults\ActionResult;
    use Zaggoware\SimpleWeb\Routing\RouteCollection;

    class Site {
        const EVENT_POST_RESOLVE_REQUEST_CACHE = "PostResolveRequestCache";
        const EVENT_EXCEPTION_RAISED = "ExceptionRaised";
        const EVENT_ERROR_RAISED = "ErrorRaised";
        const EVENT_SHUTDOWN = "Shutdown";

        /** @var Config */
        private static $config;

        /** @var RouteCollection */
        private static $routes;

        /** @var array */
        private static $eventsHandlers = array();

        /** @var RequestData */
        private static $requestData;

        /** @var IControllerFactory */
        private static $controllerFactory;

        public static function run() {
            ob_start();
            session_start();

            set_exception_handler(array("Zaggoware\\SimpleWeb\\Site", "triggerException"));
            set_error_handler(array("Zaggoware\\SimpleWeb\\Site", "triggerError"));
            register_shutdown_function(array("Zaggoware\\SimpleWeb\\Site", "triggerShutdown"));

            if (self::$config->getBasePath() !== null) {
                PathHelper::overrideBasePath(self::$config->getBasePath());
            }

            if (self::$config->getExecutionPath() !== null) {
                PathHelper::overrideExecutionPath(self::$config->getExecutionPath());
            }

            // TODO: remove ugly hack :(
            $_GET["url"] = self::getUrl();

            // HACKSSSSS
            $_SERVER = new Dictionary($_SERVER);
            if (empty($_SERVER["PATH_INFO"]) && !empty($_SERVER["ORIG_PATH_INFO"])) {
                $_SERVER->add("PATH_INFO", $_SERVER["ORIG_PATH_INFO"]);
            }

            self::$requestData = new RequestData();

            AreaRegistration::registerAllAreas();

            // Initialize route table if not already done.
            self::getRoutes()->resolveRoutes();

            if(empty(self::$controllerFactory)) {
                self::$controllerFactory = new DefaultControllerFactory();
            }

            $controller = self::$controllerFactory->resolveController(self::$routes);
            $controller->setRoutes(self::$routes);
            $controller->initialize();
            $result = self::$controllerFactory->resolveAndInvokeControllerAction($controller, self::$routes);
            $controller->cleanup();

            if($result === null) {
                ob_end_flush();
                return;
            }

            if($result instanceof ActionResult) {
                print $result->executeResult($controller);
            } else {
                print (string)$result;
            }

            ob_end_flush();
        }

        public static function onExceptionRaised($sender, $eventArgs) {
            self::triggerEvent(self::EVENT_EXCEPTION_RAISED, $sender, $eventArgs);

            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

            /** @var \Exception $exception */
            $exception = $eventArgs["Exception"];
            $exceptionTrace = $exception->getTrace();

            while ($exception !== null) {
                $message = $exception->getMessage();
                if (empty($message)) {
                    $message = "An error occurred.";
                }

                print "<h2 style=\"color:red;\">" . $message . "</h2>";
                print "<p><strong>File:</strong> " . $exception->getFile() . "<br/>";
                print "<strong>Line:</strong> " . $exception->getLine() . "<br/>";
                print "<strong>ErrorCode:</strong> " . $exception->getCode() . "</p>";

                print "<h3>Stacktrace:</h3>";
                print "<table border=1 cellspacing=0 cellpadding=3><tr><th align=left>Method</th><th align=right>File</th><th align=left>Line</th></tr>";

                foreach ($exceptionTrace as $trace) {
                    $class = isset($trace["class"]) ? $trace["class"] : '';
                    $type = isset($trace["type"]) ? $trace["type"] : '';
                    $function = isset($trace["function"]) ? $trace["function"] : '';
                    $file = isset($trace["file"]) ? $trace["file"] : '';
                    $line = isset($trace["line"]) ? $trace["line"] : '';

                    print "<tr><td>{$class}{$type}{$function}</td><td align=right>{$file}</td><td>{$line}</td></tr>";
                }

                print "</table>";

                $exception = $exception->getPrevious();
                if ($exception !== null) {
                    print "<hr/>";
                }
            }
            exit;
        }

        public static function onErrorRaised($sender, $eventArgs) {
            self::triggerEvent(self::EVENT_ERROR_RAISED, $sender, $eventArgs);

            throw new ShutdownException($eventArgs["Message"], $eventArgs["Type"], $eventArgs["File"], $eventArgs["Line"]);
        }

        public static function onShutdown($sender, $eventArgs) {
            self::triggerEvent(self::EVENT_SHUTDOWN, $sender, $eventArgs);

            $error = error_get_last();

            if($error) {
                throw new ShutdownException($error["message"], $error["type"], $error["file"], $error["line"]);
            }
        }

        public static function triggerError($type, $message, $file, $line, $context) {
            self::onErrorRaised(null, array(
                "Type" => $type,
                "Message" => $message,
                "File" => $file,
                "Line" => $line,
                "Context" => $context
            ));
        }

        public static function triggerException(\Exception $exception) {
            self::onExceptionRaised(null, array(
                "Exception" => $exception
            ));
        }

        public static function triggerShutdown() {
            self::onShutdown(null, array());
        }

        public static function triggerEvent($eventType, $sender, $eventArgs) {
            if(array_key_exists($eventType, self::$eventsHandlers)) {
                foreach(self::$eventsHandlers[$eventType] as $handler) {
                    if(is_callable($handler)) {
                        try {
                            $handler($sender, $eventArgs);
                        }
                        catch (\Exception $e) {
                            throw new \BadFunctionCallException("Invalid event handler: $eventType");
                        }
                    }
                }
            }
        }

        /**
         * Available types: request, get, post, cookie, all. You can filter on multiple types
         * by separating the types with a comma.
         *
         * @param string $requestType Available types: request, get, post, cookie, all. You can filter on multiple types
         *                            by separating the types with a comma.
         * @param string $key
         * @return IDictionary|mixed|null
         */
        public static function getRequestData($requestType = null, $key = null) {
            return self::$requestData->filter($requestType, $key);
        }

        /**
         * @return Config
         */
        public static function getConfig() {
            return self::$config;
        }

        /**
         * @param Config $config
         */
        public static function setConfig(Config $config) {
            self::$config = $config;
        }

        /**
         * @return RouteCollection
         */
        public static function getRoutes() {
            if(self::$routes === null) {
                self::$routes = new RouteCollection();
            }

            return self::$routes;
        }

        /**
         * @return IControllerFactory
         */
        public static function getControllerFactory() {
            return self::$controllerFactory;
        }

        /**
         * @param IControllerFactory $controllerFactory
         */
        public static function setControllerFactory(IControllerFactory $controllerFactory) {
            self::$controllerFactory = $controllerFactory;
        }


        /**
         * @param string $className
         */
        public static function __autoload($className) {
            if(self::$config === null) {
                throw new \RuntimeException("Cannot autoload without config.");
            }

            $namespace = self::$config->getNamespace();
            $namespaceLength = strlen($namespace);
            $classNameLength = strlen($className);

            if($className == $namespace ."\\") {
                return;
            }

            $fileName = self::$config->getBasePath();

            if($classNameLength > $namespaceLength && substr($className, 0, $namespaceLength + 1) === $namespace ."\\") {
                $className = substr($className, $namespaceLength + 1);
            } else {
                $fileName .= self::$config->getLibraryPath();
            }

            $fileName .= $className .".php";
            $fileName = str_replace("\\", "/", $fileName);

            if(strtolower(substr($className, -(strlen("controller")))) === "controller") {
                if(!file_exists($fileName)) {
                    // Let the ControllerFactory handle the error.
                    return;
                }
            }

            require_once $fileName;
        }

        private static function getUrl() {
            $pathInfo = !empty($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "/";
            if($pathInfo[0] === "/") {
                $pathInfo = strlen($pathInfo) > 1 ? substr($pathInfo, 1) : "";
            }

            return $pathInfo;
        }
    }
}

