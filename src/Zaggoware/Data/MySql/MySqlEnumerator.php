<?php

namespace Zaggoware\Data\MySql;

use Zaggoware\Generic\ArrayList;
use Zaggoware\Generic\IEnumerator;

class MySqlEnumerator implements IEnumerator {

    /** @var SimpleMySqlResult */
    private $result;

    /** @var array|null */
    private $current;

    /** @var bool */
    private $useCache = false;

    /** @var ArrayList */
    private $cachedFetchResults;

    /** @var int */
    private $cacheIndex = 0;

    public function __construct(SimpleMySqlResult $result) {
        $this->result = $result;
        $this->cachedFetchResults = new ArrayList();
    }

    public function getCurrent() {
        if ($this->useCache) {
            return $this->cachedFetchResults->elementAtOrDefault($this->cacheIndex);
        }

        return $this->current;
    }

    public function moveNext() {
        if ($this->useCache) {
            $this->cacheIndex++;

            return $this->cachedFetchResults->count() > $this->cacheIndex;
        }

        $fetched = $this->result->fetchAssoc();
        $this->current = $fetched;

        $this->cachedFetchResults->add($fetched);

        return $fetched !== null;
    }

    public function reset() {
        $this->current = null;
        $this->result = null;
        $this->useCache = true;
        $this->cacheIndex = 0;
    }
}