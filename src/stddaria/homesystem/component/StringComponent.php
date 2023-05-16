<?php

namespace stddaria\homesystem\component;

use LogicException;

class StringComponent extends Component {
    private string $mString;

    public function __construct( string $str ) {
        parent::__construct();

        $this->mString = $str;
    }

    public function extract(): string {
        return $this->mString;
    }

    public function set( mixed $var ): void {
        if( !is_string( $var ) ) {
            throw new LogicException( "Expected type of string. " . $var::class . " given!" );
        }
        $this->mString = $var;
    }
}