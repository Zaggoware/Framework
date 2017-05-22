<?php

namespace Zaggoware\SimpleWeb\ActionResults;

use Zaggoware\Generic\IEnumerable;
use Zaggoware\Helpers\JsonHelper;
use Zaggoware\SimpleWeb\IController;

class JsonResult extends ActionResult {
    /** @var mixed */
    private $object;

    /**
     * JsonResult constructor.
     * @param mixed $object
     */
    public function __construct($object) {
        $this->object = $object;
    }

    /**
     * @param IController $controller
     * @return string
     */
    public function executeResult(IController $controller) {
        //header("Content-Type: text/json");
        
        if ($this->object instanceof IEnumerable) {
            $this->object = $this->object->toArray();
        }

        return JsonHelper::serialize($this->object);
    }
}