<?php

namespace Zaggoware\SimpleWeb\ActionResults {

    use Zaggoware\SimpleWeb\IController;

    class RedirectResult extends ActionResult {
        /**
         * @param string $url
         */
        public function __construct($url) {
            $this->url = $url;
        }

        /** @var string */
        private $url;

        public function executeResult(IController $controller) {
            ob_clean();

            header('Location: '. $this->url);
            print 'Redirecting to '. $this->url .'...';

            ob_end_flush();
        }
    }
}

 