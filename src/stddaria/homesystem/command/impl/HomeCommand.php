<?php

namespace stddaria\homesystem\command\impl;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use stddaria\homesystem\command\SystemCommand;
use stddaria\homesystem\component\config\messages\HomeListComponent;
use stddaria\homesystem\SystemCore;

class HomeCommand extends SystemCommand {

    public function __construct( SystemCore $core ) {
        parent::__construct( $core, "home", "Телепортироваться в точку дома.", "/home <НазваниеДома>" );
    }

    public function execute( CommandSender $sender, $commandLabel, array $args ): void {
        if( !$this->check( $sender ) ) {
            return;
        }
        assert( $sender instanceof Player );

        $registry = $this->getCore()->getHomeRegistry();

        if( !$this->validateArgumentsCount( $args, 1 ) ) {
            $homeList = $registry->getPlayerHomes( $sender );

            $homeNames = [];

            foreach( $homeList as $entity ) {
                $homeNames[] = $entity->getHomeName();
            }
            $config = $this->getCore()->getConfig();
            $component = $config->getStringComponent( HomeListComponent::class );
            $sender->sendMessage( sprintf( $component->extract(), sizeof( $homeNames ), implode( ", ", $homeNames ) ) );
            return;
        }

        $requested = $args[ 0 ];
        $component = $registry->tryTeleportToHome( $sender, $requested );
        $sender->sendMessage( $component->extract() );
    }
}