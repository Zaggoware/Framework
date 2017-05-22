<?php

namespace Zaggoware\Generic;

interface IEnumerator
{
    /**
     * @return mixed
     */
    function getCurrent();
    function moveNext();
    function reset();
}