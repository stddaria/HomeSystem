<?php

namespace stddaria\homesystem\home\validator\impl;

use stddaria\homesystem\home\validator\HomeNameValidator;

class SpecialSymbolsValidator extends HomeNameValidator {

    public function validate( string $str ): bool {
        return !preg_match( "/['^£$%&*()}{@#~?><,|=_+¬-]/", $str);
    }
}