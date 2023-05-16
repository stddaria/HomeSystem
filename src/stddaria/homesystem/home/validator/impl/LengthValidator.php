<?php

namespace stddaria\homesystem\home\validator\impl;

use stddaria\homesystem\home\validator\HomeNameValidator;

class LengthValidator extends HomeNameValidator {
    public const MinNameLength = 3;
    public const MaxNameLength = 16; // Не изменять!

    public function validate( string $str ): bool {
        $length = strlen($str);

        return $length > self::MinNameLength && $length < self::MaxNameLength;
    }

    public function getErrorMessage(): string {
        return sprintf(parent::getErrorMessage(), self::MinNameLength, self::MaxNameLength);
    }
}