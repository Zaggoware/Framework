<?php

namespace Zaggoware\Reflection;

class AccessModifier {
    const PUBLIC_M = "public";

    const PROTECTED_M = "protected";

    const PRIVATE_M = "private";

    const DEFAULT_M = self::PUBLIC_M;

    const FIELD_DEFAULT = self::PRIVATE_M;

    const METHOD_DEFAULT = self::PUBLIC_M;

    public static function validate($accessModifier) {
        if (empty($accessModifier)) {
            return false;
        }

        return in_array(
            strtolower($accessModifier),
            array(self::PUBLIC_M, self::PROTECTED_M, self::PRIVATE_M)
        );
    }
} 