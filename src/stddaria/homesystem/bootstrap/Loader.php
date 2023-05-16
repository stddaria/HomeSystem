<?php

namespace stddaria\homesystem\bootstrap;

use pocketmine\plugin\PluginBase;
use stddaria\homesystem\SystemCore;

class Loader extends PluginBase {
    private SystemCore $mSystemCore;

    public function onLoad(): void {
        $this->mSystemCore = new SystemCore( $this );
    }

    public function onEnable() : void {
        $this->mSystemCore->init();
    }

    public function getSystemCore(): SystemCore {
        return $this->mSystemCore;
    }
}