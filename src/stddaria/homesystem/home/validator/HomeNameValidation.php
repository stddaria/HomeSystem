<?php

namespace stddaria\homesystem\home\validator;

use stddaria\homesystem\component\StringComponent;

class HomeNameValidation {
    /** @var HomeNameValidator[]  */
    private static array $mValidators = [];

    public static function validate(string $str): ?HomeNameValidator {
        $validator = null;
        foreach(self::$mValidators as $pValidator) {
            if(!$pValidator->validate($str)) {
                $validator = $pValidator;
                break;
            }
        }
        return $validator;
    }

    public static function addValidator(HomeNameValidator $validator): void {
        self::$mValidators[] = $validator;
    }

    public static function validateHomeName(string $requested): ?StringComponent {
        $validator = self::validate($requested);
        if($validator != null) {
            return new StringComponent($validator->getErrorMessage());
        }
        return null;
    }
}
