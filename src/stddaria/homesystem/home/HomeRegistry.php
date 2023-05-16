<?php

namespace stddaria\homesystem\home;

use pocketmine\Player;
use stddaria\homesystem\component\config\MaxHomesComponent;
use stddaria\homesystem\component\config\messages\exception\CanNotCreateHomeComponent;
use stddaria\homesystem\component\config\messages\exception\CanNotDeleteHomeComponent;
use stddaria\homesystem\component\config\messages\exception\CanNotValidateHomeNameComponent;
use stddaria\homesystem\component\config\messages\exception\detail\HomeAlreadyExistsComponent;
use stddaria\homesystem\component\config\messages\exception\detail\HomeNotExistsComponent;
use stddaria\homesystem\component\config\messages\exception\detail\NoFreeSlotsForHomeCreatingComponent;
use stddaria\homesystem\component\config\messages\HomeCreatedComponent;
use stddaria\homesystem\component\config\messages\HomeDeletedComponent;
use stddaria\homesystem\component\config\messages\HomeTeleportedComponent;
use stddaria\homesystem\component\config\messages\validation\exception\detail\InvalidSymbolLengthDetectedComponent;
use stddaria\homesystem\component\config\messages\validation\exception\detail\NonLatinSymbolsDetectedComponent;
use stddaria\homesystem\component\config\messages\validation\exception\detail\SpecialSymbolsDetectedComponent;
use stddaria\homesystem\component\StringComponent;
use stddaria\homesystem\config\LoadedConfig;
use stddaria\homesystem\database\HomesDatabase;
use stddaria\homesystem\home\entity\HomeEntity;
use stddaria\homesystem\home\validator\HomeNameValidation;
use stddaria\homesystem\home\validator\impl\LengthValidator;
use stddaria\homesystem\home\validator\impl\NonLatinSymbolsValidator;
use stddaria\homesystem\home\validator\impl\SpecialSymbolsValidator;
use stddaria\homesystem\SystemCore;

class HomeRegistry {
    private SystemCore $mCore;
    private HomesDatabase $mDatabase;
    /** @var HomeEntity[] */
    private array $mLoadedHomes = [];
    /** @var array<string, HomeEntity[]> */
    private array $mIndexedHomes = [];
    private LoadedConfig $mConfig;

    public function __construct( SystemCore $core ) {
        $this->mCore = $core;
        $this->mDatabase = new HomesDatabase( $core->getLoader()->getDataFolder() . "player_homes.sqlite3" );
    }

    public function init(): void {
        $this->mDatabase->init();
        $this->mLoadedHomes = $this->mDatabase->getHomes();
        $this->mConfig = $config = $this->mCore->getConfig();

        HomeNameValidation::addValidator( new LengthValidator($config->getStringComponent(InvalidSymbolLengthDetectedComponent::class)) );
        HomeNameValidation::addValidator( new SpecialSymbolsValidator($config->getStringComponent(SpecialSymbolsDetectedComponent::class)) );
        HomeNameValidation::addValidator( new NonLatinSymbolsValidator($config->getStringComponent(NonLatinSymbolsDetectedComponent::class)) );
    }

    public function indexPlayer( Player $player ): void {
        $pName = mb_strtolower( $player->getName() );
        $pObjectHash = spl_object_hash( $player );
        foreach( $this->mLoadedHomes as $home ) {
            if( mb_strtolower( $home->getPlayerName() ) == $pName ) {
                $this->mIndexedHomes[ $pObjectHash ][] = $home;
            }
        }
    }

    public function deIndexPlayer( Player $player ): void {
        $pObjectHash = spl_object_hash( $player );
        unset( $this->mIndexedHomes[ $pObjectHash ] );
    }

    public function reIndexPlayer( Player $player ): void {
        $this->deIndexPlayer( $player );
        $this->indexPlayer( $player );
    }

    /**
     * @return HomeEntity[]
     */
    public function getPlayerHomes( Player $player ): array {
        $pObjectHash = spl_object_hash( $player );
        if( !isset( $this->mIndexedHomes[ $pObjectHash ] ) ) {
            return [];
        }
        return $this->mIndexedHomes[ $pObjectHash ];
    }

    public function tryGetHome( Player $player, string $request ): ?HomeEntity {
        $array = $this->getPlayerHomes( $player );
        if( $array == null ) {
            return null;
        }
        foreach( $array as $entity ) {
            if( $entity->getHomeName() == $request ) {
                return $entity;
            }
        }
        return null;
    }

    public function pushHome( HomeEntity $entity ): void {
        // Добавление точки дома
        // в кэш.
        $this->mLoadedHomes[] = $entity;

        $player = $this->mCore->getLoader()->getServer()->getPlayerExact( $entity->getPlayerName() );
        if( $player != null ) {
            $this->deIndexPlayer( $player );
            $this->indexPlayer( $player );
        }
        // Запись в базу данных.
        $this->mDatabase->pushHome( $entity );
    }

    public function deleteHome( HomeEntity $entity ): void {
        // Удаление точки дома
        // из кэша.
        foreach( $this->mLoadedHomes as $index => $homeEntity ) {
            if($homeEntity === $entity) {
                unset($this->mLoadedHomes[$index]);
                break;
            }
        }

        $player = $this->mCore->getLoader()->getServer()->getPlayerExact( $entity->getPlayerName() );
        if( $player != null ) {
            $this->reIndexPlayer($player);
        }
        // Удаление из базы данных.
        $this->mDatabase->deleteHome($entity->getPlayerName(), $entity->getHomeName());
    }

    public function tryDeleteHome( Player $player, string $request ): StringComponent {
        $home = $this->tryGetHome( $player, $request );
        if( $home == null ) {
            $exceptionComponent = $this->mConfig->getStringComponent(CanNotDeleteHomeComponent::class);
            $nonExistsComponent = $this->mConfig->getStringComponent(HomeNotExistsComponent::class);

            return new StringComponent( sprintf("%s: %s", $exceptionComponent->extract(), $nonExistsComponent->extract()) );
        }

        $this->deleteHome($home);

        $deletedComponent = $this->mConfig->getStringComponent(HomeDeletedComponent::class);
        return new StringComponent(sprintf($deletedComponent->extract(), $home->getHomeName()));
    }

    public function tryCreateHome( Player $player, string $request ): StringComponent {
        $home = $this->tryGetHome( $player, $request );
        if( $home != null ) {
            $exceptionComponent = $this->mConfig->getStringComponent(CanNotCreateHomeComponent::class);
            $existsComponent = $this->mConfig->getStringComponent(HomeAlreadyExistsComponent::class);

            return new StringComponent( sprintf("%s: %s", $exceptionComponent->extract(), $existsComponent->extract()) );
        }

        $vaComponent = HomeNameValidation::validateHomeName( $request );
        if( $vaComponent != null ) {
            $canNotValidateComponent = $this->mConfig->getStringComponent(CanNotValidateHomeNameComponent::class);
            return new StringComponent(sprintf("%s: %s", $canNotValidateComponent->extract(), $vaComponent->extract()));
        }

        $homeList = $this->getPlayerHomes($player);
        $maxHomesComponent = $this->mConfig->tryGetComponent(MaxHomesComponent::class);
        if(sizeof($homeList) >= $maxHomesComponent->extract()) {
            $limitComponent = $this->mConfig->getStringComponent(NoFreeSlotsForHomeCreatingComponent::class);
            return new StringComponent(sprintf($limitComponent->extract(), $maxHomesComponent->extract()));
        }

        $pPos = $player->getPosition();
        $this->pushHome( new HomeEntity( $player->getName(), $request, $pPos->asVector3(), $player->getLevel()->getName() ) );

        $createdHomeComponent = $this->mConfig->getStringComponent(HomeCreatedComponent::class);
        return new StringComponent( sprintf( $createdHomeComponent->extract(), $request ) );
    }

    public function tryTeleportToHome(Player $player, string $request) : StringComponent {
        $home = $this->tryGetHome($player, $request);
        if($home == null) {
            return $this->mConfig->getStringComponent(HomeNotExistsComponent::class);
        }

        $hComponent = $home->tryTeleport($player, $this->mConfig);
        if($hComponent != null) {
            return $hComponent;
        }

        $teleportedComponent = $this->mConfig->getStringComponent(HomeTeleportedComponent::class);
        return new StringComponent(sprintf($teleportedComponent->extract(), $home->getHomeName()));
    }

}