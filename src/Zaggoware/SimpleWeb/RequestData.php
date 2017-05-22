<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Generic\Dictionary;
    use Zaggoware\Generic\IDictionary;

    class RequestData {
        public function __construct($useSuperGlobals = true) {
            $this->requestCollection = new Dictionary($useSuperGlobals ? $_REQUEST : null);
            $this->getCollection = new Dictionary($useSuperGlobals ? $_GET : null);
            $this->postCollection = new Dictionary($useSuperGlobals ? $_POST : null);
            $this->cookieCollection = new Dictionary($useSuperGlobals ? $_COOKIE : null);

            if($useSuperGlobals) {
                $this->requestCollection->makeReadOnly();
                $this->getCollection->makeReadOnly();
                $this->postCollection->makeReadOnly();
                $this->cookieCollection->makeReadOnly();
            }
        }

        /** @var IDictionary */
        private $requestCollection;

        /** @var IDictionary */
        private $getCollection;

        /** @var IDictionary */
        private $postCollection;

        /** @var IDictionary */
        private $cookieCollection;

        /**
         * Available types: request, get, post, cookie, all. You can filter on multiple types
         * by separating the types with a comma.
         *
         * @param string $requestType Available types: request, get, post, cookie, all. You can filter on multiple types
         *                            by separating the types with a comma.
         * @param string $key
         * @return IDictionary|mixed|null
         */
        public function filter($requestType = null, $key = null) {
            $data = array();
            $types = array();

            if(!empty($requestType)) {
                $types = explode(",", $requestType);
            }

            if(empty($requestType) || empty($types)) {
                $types = array("all");
            }

            foreach($types as $type) {
                switch ($type) {
                    case null:
                    case "all":
                    case "request":
                    case "":
                        $collection = $this->requestCollection;
                        break;

                    case "get":
                        $collection = $this->getCollection;
                        break;

                    case "post":
                        $collection = $this->postCollection;
                        break;

                    case "cookie":
                        $collection = $this->cookieCollection;
                        break;

                    default:
                        throw new \InvalidArgumentException("Request type '$requestType' could not be recognized.'");
                }

                if (!empty($key)) {
                    $value = null;
                    $collection->tryGetValue($key, $value);

                    return $value;
                }

                $data = array_merge($data, $collection->toArray());
            }

            return new Dictionary($data);
        }
    }
}

 