<?php

namespace Zaggoware\Generic {

    interface IList extends ICollection, \ArrayAccess {

        /**
         * Adds an item to the IList.
         *
         * @param mixed $item
         */
        function add($item);

        /**
         * Removes all items from the IList.
         */
        function clear();

        /**
         * Determines whether the IList contains a specific value.
         *
         * @param mixed $item
         * @return bool
         */
        function contains($item);

        /**
         * Determines the index of a specific item in the IList.
         *
         * @param mixed $item
         * @return int
         */
        function indexOf($item);

        /**
         * Inserts an item to the IList at the specified index.
         *
         * @param int $index
         * @param mixed $item
         */
        function insert($index, $item);

        /**
         * Gets a value indicating whether the IList is read-only.
         *
         * @return mixed
         */
        function isReadOnly();

        /**
         * Removes the first occurrence of a specific object from the IList.
         *
         * @param mixed $item
         */
        function remove($item);

        /**
         * Removes the IList item at the specified index.
         *
         * @param int $index
         */
        function removeAt($index);
    }
}

 