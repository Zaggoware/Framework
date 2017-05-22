<?php

namespace Zaggoware\SimpleWeb {

    class TagRenderMode {

        /** Example: <a href="http://example.com">Example</a>. */
        const NORMAL = 0;

        /** Example: <span> */
        const START_TAG = 1;

        /** Example: </span> */
        const END_TAG = 2;

        /** Example: <img src="example.png" /> */
        const SELF_CLOSING = 3;
    }
}

 