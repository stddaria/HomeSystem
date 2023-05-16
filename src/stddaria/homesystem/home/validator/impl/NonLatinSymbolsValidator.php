<?php

namespace stddaria\homesystem\home\validator\impl;

use stddaria\homesystem\home\validator\HomeNameValidator;

class NonLatinSymbolsValidator extends HomeNameValidator {

    public function validate( string $str ): bool {
        return preg_match( '/^[\p{Latin}\s\d]+$/u', $str );
    }
}