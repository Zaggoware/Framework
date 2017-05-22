<?php

namespace Zaggoware\Helpers {

    use Zaggoware\SimpleWeb\TagBuilder;
    use Zaggoware\SimpleWeb\TagRenderMode;

    class FormMethod {
        const POST = "POST";
        const GET = "GET";
    }

    class HtmlHelper {
        /** @var string */
        private static $idAttributeDotReplacement;

        public static function getIdAttributeDotReplacement() {
            if (empty(self::$idAttributeDotReplacement)) {
                self::$idAttributeDotReplacement = "_";
            }

            return self::$idAttributeDotReplacement;
        }

        public static function setIdAttributeDotReplacement($value) {
            self::$idAttributeDotReplacement = $value;
        }

        public function beginForm($action = null, $controller = null, $method = FormMethod::POST, $routeValues = null, $htmlAttributes = null) {
            $url = UrlHelper::action($action, $controller, $routeValues);

            $form = new TagBuilder("form");
            $attrs = $form->getAttributes();
            $attrs->add("action", $url);
            $attrs->add("method", $method);

            foreach ($htmlAttributes as $htmlAttr => $value) {
                $attrs->add($htmlAttr, $value);;
            }

            return $form->toString(TagRenderMode::START_TAG);
        }

        public function endForm() {
            $form = new TagBuilder("form");

            return $form->toString(TagRenderMode::END_TAG);
        }

        public function label($text, $htmlAttributes = null) {
            $label = new TagBuilder("label");
            $label->setInnerText($text);
            $attrs = $label->getAttributes();

            foreach ($htmlAttributes as $htmlAttr => $value) {
                $attrs->add($htmlAttr, $value);;
            }

            return $label->toString();
        }

        public function labelFor(callable $expression, $htmlAttributes = null) {
            $label = new TagBuilder("label");

            $text = $expression->toString(); // TODO: get display attribute?

            $label->setInnerText($text)
                ->setAttribute("for", $expression->getName())
                ->addAttributes($htmlAttributes);

            return $label->toString();
        }

        public function textbox($name, $value = null, $htmlAttributes = null) {
            $textbox = new TagBuilder("input");
            $textbox->setAttribute("type", "text");
        }
    }
}

 