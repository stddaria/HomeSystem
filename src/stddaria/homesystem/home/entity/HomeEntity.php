<?php

namespace stddaria\homesystem\home\entity;

use homesystem\component\config\messages\exception\CanNotTeleportToHomeComponent;
use homesystem\component\config\messages\exception\detail\WorldNotLoadedComponent;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\Player;
use stddaria\homesystem\component\entity\EntityWithComponents;
use stddaria\homesystem\component\home\HomeNameComponent;
use stddaria\homesystem\component\home\PlayerNameComponent;
use stddaria\homesystem\component\home\Vector3Component;
use stddaria\homesystem\component\home\WorldNameComponent;
use stddaria\homesystem\component\StringComponent;
use stddaria\homesystem\config\LoadedConfig;

class HomeEntity extends EntityWithComponents {
    public function __construct( string $playerName, string $homeName, Vector3 $pos, string $worldName ) {
        $this->pushOrReplaceComponent( new PlayerNameComponent( $playerName ) );
        $this->pushOrReplaceComponent( new HomeNameComponent( $homeName ) );
        $this->pushOrReplaceComponent( new Vector3Component( $pos ) );
        $this->pushOrReplaceComponent( new WorldNameComponent( $worldName ) );
    }

    public function tryTeleport( Player $player, LoadedConfig $config ): ?StringComponent {
        $server = $player->getServer();

        $worldName = $this->getWorldName();
        $world = $server->getLevelByName( $worldName );
        if( $world == null ) {
            $cannotTeleportComponent = $config->getStringComponent(CanNotTeleportToHomeComponent::class);
            $worldNotLoadedComponent = $config->getStringComponent(WorldNotLoadedComponent::class);

            return new StringComponent( sprintf("%s: %s", $cannotTeleportComponent->extract(), $worldNotLoadedComponent->extract()) );
        }
        $player->teleport( Location::fromObject( $this->getPos(), $world ) );
        return null;
    }

    public function getPos(): Vector3 {
        $component = $this->tryGetComponent( Vector3Component::class );
        assert( $component instanceof Vector3Component );

        return $component->extract();
    }

    public function getPlayerName(): string {
        $component = $this->tryGetComponent( PlayerNameComponent::class );
        assert( $component instanceof StringComponent );

        return $component->extract();
    }

    public function getHomeName(): string {
        $component = $this->tryGetComponent( HomeNameComponent::class );
        assert( $component instanceof StringComponent );

        return $component->extract();
    }

    public function getWorldName(): string {
        $component = $this->tryGetComponent( WorldNameComponent::class );
        assert( $component instanceof StringComponent );

        return $component->extract();
    }
}
