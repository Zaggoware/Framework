<?php

namespace Zaggoware\Generic {

    interface ICollection extends IEnumerable {

        /**
         * Gets the number of elements contained in the ICollection.
         *
         * @param callable|null $predicate
         * @return int
         */
        function count($predicate = null);

        /**
         * Copies the elements of the ICollection to an array, starting at a particular array index.
         *
         * @param array $array
         * @param int $index
         */
        function copyTo(array &$array, $index);
    }
}

 