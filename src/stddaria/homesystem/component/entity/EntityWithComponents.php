<?php

namespace stddaria\homesystem\component\entity;

use stddaria\homesystem\component\Component;

class EntityWithComponents {
    /** @var array<string, Component> */
    private array $mComponents;

    public function tryGetComponent( string $cClass ): ?Component {
        return $this->mComponents[ $cClass ] ?? null;
    }

    public function tryPushComponent( Component $component ): bool {
        if( isset( $this->mComponents[ $component::class ] ) ) {
            return false;
        }
        $this->pushOrReplaceComponent( $component );
        return true;
    }

    public function pushOrReplaceComponent( Component $component ): void {
        $this->mComponents[ $component::class ] = $component;
    }
}