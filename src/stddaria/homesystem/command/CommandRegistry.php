<?php

namespace stddaria\homesystem\command;

use stddaria\homesystem\command\impl\DeleteHomeCommand;
use stddaria\homesystem\command\impl\HomeCommand;
use stddaria\homesystem\command\impl\SetHomeCommand;
use stddaria\homesystem\SystemCore;

class CommandRegistry {
    private SystemCore $mSys;

    public function __construct( SystemCore $core ) {
        $this->mSys = $core;
    }

    public function setup(): void {
        $loader = $this->mSys->getLoader();
        $cmdMap = $loader->getServer()->getCommandMap();
        if( $cmdMap == null ) {
            $loader->getLogger()->critical( "Could not obtain CommandMap... Disabling plugin..." );
            $loader->getServer()->getPluginManager()->disablePlugin( $loader );
            return;
        }
        $prefix = "tp_system_prefix";

        $cmdMap->register( $prefix, new HomeCommand( $this->mSys ), null, true );
        $cmdMap->register( $prefix, new SetHomeCommand( $this->mSys ), null, true );
        $cmdMap->register( $prefix, new DeleteHomeCommand( $this->mSys ), null, true );
    }
}