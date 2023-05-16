<?php

namespace stddaria\homesystem\home\validator;

use stddaria\homesystem\component\StringComponent;

abstract class HomeNameValidator {
    private StringComponent $mComponent;

    public function __construct( StringComponent $component ) {
        $this->mComponent = $component;
    }

    public abstract function validate( string $str ): bool;

    public function getErrorMessage(): string {
        return $this->mComponent->extract();
    }
}