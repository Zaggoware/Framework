<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Generic\Dynamic;

    interface IView {
        /**
         * @param array $viewData
         * @param array $tempData
         * @param Dynamic $viewBag
         * @return string
         */
        function render(array $viewData, array $tempData, Dynamic $viewBag);
    }
}

 