<?php

namespace stddaria\homesystem\component;

abstract class Component {
    protected function __construct() {
        // No-op.
    }

    abstract public function extract(): mixed;

    abstract public function set( mixed $var ): void;
}