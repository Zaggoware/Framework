<?php

namespace Zaggoware\Data\Entity;

use Zaggoware\Generic\IEnumerable;
use Zaggoware\Linq\EnumerableTrait;

abstract class DbResult implements IEnumerable {
    use EnumerableTrait;

    public abstract function fetch();

    public abstract function fetchAssoc();

    public abstract function fetchAll();

    public abstract function fetchAllAssoc();

    public abstract function getRowCount();

    public abstract function getInsertId();

    public abstract function resetPointer();
}
 