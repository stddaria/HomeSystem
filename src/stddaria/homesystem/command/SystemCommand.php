<?php

namespace stddaria\homesystem\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use stddaria\homesystem\SystemCore;

abstract class SystemCommand extends Command {
    private SystemCore $mCore;

    public function __construct( SystemCore $core, $name, $description = "", $usageMessage = null, array $aliases = [] ) {
        parent::__construct( $name, $description, $usageMessage, $aliases );

        $this->mCore = $core;
    }

    public function check( CommandSender $sender, bool $isPlayer = true ): bool {
        $permission = $this->getPermission();
        if( $permission != null ) {
            if( !$this->testPermission( $sender ) ) {
                return false;
            }
        }
        if( $isPlayer ) {
            if( !( $sender instanceof Player ) ) {
                $sender->sendMessage( "Эта команда предназначена для игрока." );
                return false;
            }
        }
        return true;
    }

    /**
     * @param string[] $args
     */
    public function validateArgumentsCount( array $args, int $minCount ): bool {
        return sizeof( $args ) >= $minCount;
    }

    public function sendUsage( CommandSender $sender ): void {
        $sender->sendMessage( sprintf( "Использование: %s", $this->getUsage() ) );
    }

    public function getCore(): SystemCore {
        return $this->mCore;
    }
}