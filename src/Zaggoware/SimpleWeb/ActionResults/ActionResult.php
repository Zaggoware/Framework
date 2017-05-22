<?php

namespace Zaggoware\SimpleWeb\ActionResults {

    use Zaggoware\SimpleWeb\IController;

    abstract class ActionResult {

        /**
         * @param IController $controller
         * @return mixed
         */
        public abstract function executeResult(IController $controller);
    }
}

 