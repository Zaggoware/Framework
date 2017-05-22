<?php

namespace Zaggoware\SimpleWeb {

    use Zaggoware\Generic\Dictionary;
    use Zaggoware\Generic\IDictionary;
    use Zaggoware\Helpers\HtmlHelper;

    class TagBuilder {

        #region Private Fields

        /** @var string */
        private $idAttributeDotReplacement;

        /** @var string */
        private $innerHtml;

        /** @var IDictionary */
        private $attributes;

        /** @var string */
        private $tagName;

        #endregion

        #region Constructors and Destructors

        /**
         * Initializes a new instance of the TagBuilder.
         *
         * @param string $tagName
         * @throws \InvalidArgumentException
         */
        public function __construct($tagName) {
            if(empty($tagName)) {
                throw new \InvalidArgumentException("Argument 'tagName' cannot be empty.");
            }

            $this->tagName = $tagName;
            $this->attributes = new Dictionary();
        }

        #endregion

        #region Getter and Setter Methods

        /**
         * Gets the HTML attribute dictionary.
         *
         * @return IDictionary
         */
        public function getAttributes() {
            return $this->attributes;
        }

        /**
         * Sets the replacement string for dots within the id attribute.
         *
         * @param mixed $value
         */
        public function setIdAttributeDotReplacement($value) {
            $this->idAttributeDotReplacement = $value;
        }

        /**
         * Gets the replacement string for dots within the id attribute.
         *
         * @return string
         */
        public function getIdAttributeDotReplacement() {
            if(empty($this->idAttributeDotReplacement)) {
                $this->idAttributeDotReplacement = HtmlHelper::getIdAttributeDotReplacement();
            }

            return $this->idAttributeDotReplacement;
        }

        /**
         * Sets the inner HTML.
         *
         * @param string $innerHtml
         * @return $this
         */
        public function setInnerHtml($innerHtml) {
            $this->innerHtml = $innerHtml;

            return $this;
        }

        /**
         * Gets the inner HTML.
         *
         * @return string
         */
        public function getInnerHtml() {
            return !empty($this->innerHtml) ? $this->innerHtml : '';
        }

        /**
         * Gets the tag name.
         *
         * @return string
         */
        public function getTagName() {
            return $this->tagName;
        }

        #endregion

        #region Public Methods

        /**
         * Adds the CSS class.
         *
         * @param string $value
         * @return $this|void
         */
        public function addCssClass($value) {
            $str = null;
            if ($this->attributes->tryGetValue("class", $str)) {
                $this->attributes->set("class", "$value $str");
                return $this;
            }

            $this->attributes->set("class", $value);

            return $this;
        }

        /**
         * Creates a sanitized id.
         *
         * @param $originalId
         * @param null $invalidCharReplacement
         * @return null|string
         */
        public static function createSanitizedId($originalId, $invalidCharReplacement = null) {
            if($invalidCharReplacement === null) {
                $invalidCharReplacement = HtmlHelper::getIdAttributeDotReplacement();
            }

            if(empty($originalId) || !is_string($originalId)) {
                return null;
            }

            $c = $originalId[0];
            if(!self::isLetter($c)) {
                return null;
            }

            $sanitizedId = $c;

            for($i = 1; $i < strlen($originalId); $i++) {
                $c = $originalId[$i];

                if(self::isValidIdCharacter($c)) {
                    $sanitizedId .= $c;
                } else {
                    $sanitizedId .= $invalidCharReplacement;
                }
            }

            return $sanitizedId;
        }

        /**
         * Generates a sanitized id.
         *
         * @param $name
         * @return $this
         */
        public function generateId($name) {
            if (!$this->attributes->containsKey("id")) {
                $value = self::createSanitizedId($name, $this->idAttributeDotReplacement);

                if(!empty($value)) {
                    $this->attributes->set("id", $value);
                }
            }

            return $this;
        }

        /**
         * Merges the given HTML attribute with the attributes that are already present.
         *
         * @param string $key
         * @param string $value
         * @param bool $replaceExisting
         * @return $this
         * @throws \InvalidArgumentException
         */
        public function mergeAttribute($key, $value, $replaceExisting = false) {
            if(empty($key)) {
                throw new \InvalidArgumentException("Argument 'key' cannot be empty.");
            }

            if(!is_string($key)) {
                throw new \InvalidArgumentException("Argument 'key' must be a string.");
            }

            $key = str_replace('_', '-', $key);

            if($replaceExisting || !$this->attributes->containsKey($key)) {
                $this->attributes->set($key, $value);
            }

            return $this;
        }

        /**
         * Merges the given HTML attributes with the attributes that are already present.
         *
         * @param IDictionary|array $attributes
         * @param bool $replaceExisting
         * @return $this
         */
        public function mergeAttributes($attributes, $replaceExisting = false) {
            if(!empty($attributes)) {
                foreach($attributes as $key => $value) {
                    $this->mergeAttribute($key, $value, $replaceExisting);
                }
            }

            return $this;
        }

        /**
         * Sets the inner text by encoding any HTML characters.
         *
         * @param $innerText
         * @return $this
         */
        public function setInnerText($innerText) {
            $this->innerHtml = htmlspecialchars(htmlentities($innerText));

            return $this;
        }

        /**
         * Renders the HTML-tag.
         *
         * @param int|number $renderMode One of the TagRenderMode constants.
         * @return string
         * @throws \InvalidArgumentException
         */
        public function toString($renderMode = TagRenderMode::NORMAL) {
            if(!is_numeric($renderMode)) {
                throw new \InvalidArgumentException("Argument 'renderMode' must be a number (See TagRenderMode).");
            }

            switch($renderMode) {
                case TagRenderMode::START_TAG:
                    return "<{$this->tagName}". $this->renderAttributes() .'>';

                case TagRenderMode::END_TAG:
                    return "</{$this->tagName}>";

                case TagRenderMode::SELF_CLOSING:
                    return "<{$this->tagName}". $this->renderAttributes() .' />';

                default:
                    return $this->toString(TagRenderMode::START_TAG)
                        . $this->innerHtml
                        . $this->toString(TagRenderMode::END_TAG);
            }
        }

        /**
         * Renders the HTML-tag.
         *
         * @return string
         */
        public function __toString() {
            return $this->toString();
        }

        /**
         * Returns an instance of IHtmlString containing the rendered HTML-element.
         *
         * @param int $renderMode One of the TagRenderMode constants.
         * @return IHtmlString
         */
        public function toHtmlString($renderMode = TagRenderMode::NORMAL) {
            return new HtmlString($this->toString($renderMode));
        }

        #endregion

        #region Private Methods

        /**
         * @param string $c
         * @return bool
         */
        private static function isAllowableSpecialCharacter($c) {
            return $c === '-' || $c === ':' || $c === '_';
        }

        /**
         * @param string|number $c
         * @return bool
         */
        private static function isDigit($c) {
            return is_numeric($c);
        }

        /**
         * @param string $c
         * @return bool
         */
        private static function isLetter($c) {
            return ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z');
        }

        /**
         * @param string $c
         * @return bool
         */
        private static function isValidIdCharacter($c) {
            return self::isLetter($c) || self::isDigit($c) || self::isAllowableSpecialCharacter($c);
        }

        /**
         * @return string
         */
        private function renderAttributes() {
            $result = '';

            foreach($this->attributes as $key => $value) {
                if($key !== "id" || !empty($value)) {
                    $value = htmlspecialchars($value);
                    $result .= " $key=\"$value\"";
                }
            }

            return $result;
        }

        #endregion
    }
}

 