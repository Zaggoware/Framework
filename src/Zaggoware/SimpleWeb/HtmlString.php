<?php

namespace Zaggoware\SimpleWeb {

    class HtmlString implements IHtmlString {

        #region Constructors and Destructors

        /**
         * Initializes a new instance of the HtmlString.
         *
         * @param string $value
         */
        public function __construct($value) {
            $this->htmlString = $value;
        }

        #endregion

        #region Private Fields

        /** @var string*/
        private $htmlString;

        #endregion

        #region Public Methods

        /**
         * Returns an HTML-encoded string.
         *
         * @return string
         */
        public function toHtmlString() {
            return $this->htmlString;
        }

        /**
         * Returns an HTML-encoded string.
         *
         * @return string
         */
        public function __toString() {
            return $this->htmlString;
        }

        #endregion
    }
}

 