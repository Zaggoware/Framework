<?php

namespace Zaggoware\SimpleWeb\Routing {

    use Zaggoware\Helpers\UrlHelper;

    class DynamicRoute extends Route {
        public function __construct($controller, $action) {
            parent::__construct("", $controller, $action);
        }
    }
}

 