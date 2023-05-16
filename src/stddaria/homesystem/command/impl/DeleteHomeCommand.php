<?php

namespace stddaria\homesystem\command\impl;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use stddaria\homesystem\command\SystemCommand;
use stddaria\homesystem\SystemCore;

class DeleteHomeCommand extends SystemCommand {

    public function __construct( SystemCore $core ) {
        parent::__construct( $core, "deletehome", "Удалить точку дома.", "/delhome <НазваниеДома>", [ "delhome" ] );
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
        $component = $registry->tryDeleteHome( $sender, $requested );
        $sender->sendMessage( $component->extract() );
    }
}