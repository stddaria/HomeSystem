<?php

namespace stddaria\homesystem\component;

use LogicException;

class IntComponent extends Component {
    private int $mInt;

    public function __construct( int $int ) {
        parent::__construct();

        $this->mInt = $int;
    }

    public function extract(): int {
        return $this->mInt;
    }

    public function set( mixed $var ): void {
        if( !is_int( $var ) ) {
            throw new LogicException( "Expected type of int. " . $var::class . " given!" );
        }
        $this->mInt = $var;
    }
}