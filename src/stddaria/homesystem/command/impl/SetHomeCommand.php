<?php

namespace stddaria\homesystem\command\impl;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use stddaria\homesystem\command\SystemCommand;
use stddaria\homesystem\SystemCore;

class SetHomeCommand extends SystemCommand {

    public function __construct( SystemCore $core ) {
        parent::__construct( $core, "sethome", "Установить точку дома.", "/sethome <НазваниеДома>", [ "createhome" ] );
    }

    public function execute( CommandSender $sender, $commandLabel, array $args ) {
        if( !$this->check( $sender ) ) {
            return;
        }
        assert( $sender instanceof Player );

        if( !$this->validateArgumentsCount( $args, 1 ) ) {
            $this->sendUsage( $sender );
            return;
        }
        $registry = $this->getCore()->getHomeRegistry();

        $requested = $args[ 0 ];
        $component = $registry->tryCreateHome( $sender, $requested );
        $sender->sendMessage( $component->extract() );
    }
}