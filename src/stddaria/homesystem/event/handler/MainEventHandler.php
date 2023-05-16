<?php

namespace stddaria\homesystem\event\handler;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use stddaria\homesystem\SystemCore;

class MainEventHandler implements Listener {
    private SystemCore $mSystemCore;

    public function __construct( SystemCore $core ) {
        $this->mSystemCore = $core;
    }

    /**
     * @priority HIGHEST
     * @handleCancelled false
     */
    public function handlePlayerJoin( PlayerJoinEvent $event ): void {
        $this->mSystemCore->getHomeRegistry()->indexPlayer( $event->getPlayer() );
    }

    /**
     * @priority HIGHEST
     * @handleCancelled false
     */
    public function handlePlayerQuit( PlayerQuitEvent $event ): void {
        $this->mSystemCore->getHomeRegistry()->deIndexPlayer( $event->getPlayer() );
    }
}