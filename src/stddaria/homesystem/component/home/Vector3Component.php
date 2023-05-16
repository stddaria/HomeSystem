<?php

namespace stddaria\homesystem\component\home;

use LogicException;
use pocketmine\math\Vector3;
use stddaria\homesystem\component\Component;

class Vector3Component extends Component {
    private Vector3 $mVector3;

    public function __construct( Vector3 $vec ) {
        parent::__construct();

        $this->mVector3 = $vec;
    }

    public function extract(): Vector3 {
        return $this->mVector3;
    }

    public function set( mixed $var ): void {
        if( !( $var instanceof Vector3 ) ) {
            throw new LogicException( "Expected type of " . Vector3::class . ". " . $var::class . " given!" );
        }
        $this->mVector3 = $var;
    }
}