<?php

namespace stddaria\homesystem\config;

use homesystem\component\config\messages\exception\CanNotTeleportToHomeComponent;
use homesystem\component\config\messages\exception\detail\WorldNotLoadedComponent;
use http\Exception\RuntimeException;
use pocketmine\utils\Config;
use stddaria\homesystem\component\config\MaxHomesComponent;
use stddaria\homesystem\component\config\messages\exception\CanNotCreateHomeComponent;
use stddaria\homesystem\component\config\messages\exception\CanNotDeleteHomeComponent;
use stddaria\homesystem\component\config\messages\exception\CanNotValidateHomeNameComponent;
use stddaria\homesystem\component\config\messages\exception\detail\HomeAlreadyExistsComponent;
use stddaria\homesystem\component\config\messages\exception\detail\HomeNotExistsComponent;
use stddaria\homesystem\component\config\messages\exception\detail\NoFreeSlotsForHomeCreatingComponent;
use stddaria\homesystem\component\config\messages\HomeCreatedComponent;
use stddaria\homesystem\component\config\messages\HomeDeletedComponent;
use stddaria\homesystem\component\config\messages\HomeListComponent;
use stddaria\homesystem\component\config\messages\HomeTeleportedComponent;
use stddaria\homesystem\component\config\messages\validation\exception\detail\InvalidSymbolLengthDetectedComponent;
use stddaria\homesystem\component\config\messages\validation\exception\detail\NonLatinSymbolsDetectedComponent;
use stddaria\homesystem\component\config\messages\validation\exception\detail\SpecialSymbolsDetectedComponent;
use stddaria\homesystem\component\entity\EntityWithComponents;
use stddaria\homesystem\component\StringComponent;

class LoadedConfig extends EntityWithComponents {
    private Config $mConfig;

    public function __construct( Config $config ) {
        $this->mConfig = $config;

        $config->setDefaults( [
            "max_homes" => 8,
            "messages" => [
                "exception" => [
                    "can_not_create_home" => "Не удалось создать точку дома",
                    "can_not_delete_home" => "Не удалось удалить точку дома",
                    "can_not_validate_home_name" => "Не удалось проверить название точки дома",
                    "can_not_teleport_to_home" => "Не удалось телепортироваться на точку дома"
                ],
                "exception_detail" => [
                    "home_already_exists" => "Точка дома с таким названием уже существует.",
                    "home_not_exists" => "Точки дома с таким названием не существует.",
                    "no_free_slots_for_home_creating" => "Вы уже установили максимальное колчество(%s) точек дома.",
                    "world_not_loaded" => "Мир, в котором находится точка дома не загружен."
                ],
                "validation_exception_detail" => [
                    "special_symbols_detected" => "В названии точки дома не должно быть специальных символов.",
                    "non_latin_symbols_detected" => "В названии точки дома должны быть только латинские буквы и цифры.",
                    "invalid_symbol_length_detected" => "Длина названия точки дома должна быть не меньше %s символов или не больше %s символов.",
                ],
                "home_teleported" => "Вы телепортировались на точку дома с названием: %s.",
                "home_created" => "Вы создали точку дома с названием: %s.",
                "home_deleted" => "Вы удалили точку дома с названием: %s.",
                "home_list" => "Список Ваших точек дома[%s отображено]: %s."
            ],
        ] );
        $config->save();
        $config->reload();
    }

    public function load(): void {
        $array = $this->mConfig->getAll();

        $this->pushOrReplaceComponent( new MaxHomesComponent( $array[ "max_homes" ] ) );

        $messages = $array[ "messages" ];

        $this->pushOrReplaceComponent( new HomeTeleportedComponent( $messages[ "home_teleported" ] ) );
        $this->pushOrReplaceComponent( new HomeCreatedComponent( $messages[ "home_created" ] ) );
        $this->pushOrReplaceComponent( new HomeDeletedComponent( $messages[ "home_deleted" ] ) );
        $this->pushOrReplaceComponent( new HomeListComponent( $messages[ "home_list" ] ) );

        $exception = $messages[ "exception" ];

        $this->pushOrReplaceComponent( new CanNotCreateHomeComponent( $exception[ "can_not_create_home" ] ) );
        $this->pushOrReplaceComponent( new CanNotDeleteHomeComponent( $exception[ "can_not_delete_home" ] ) );
        $this->pushOrReplaceComponent( new CanNotValidateHomeNameComponent( $exception[ "can_not_validate_home_name" ] ) );
        $this->pushOrReplaceComponent( new CanNotTeleportToHomeComponent( $exception[ "can_not_teleport_to_home" ] ) );

        $eDetail = $messages[ "exception_detail" ];

        $this->pushOrReplaceComponent( new HomeAlreadyExistsComponent( $eDetail[ "home_already_exists" ] ) );
        $this->pushOrReplaceComponent( new HomeNotExistsComponent( $eDetail[ "home_not_exists" ] ) );
        $this->pushOrReplaceComponent( new NoFreeSlotsForHomeCreatingComponent( $eDetail[ "no_free_slots_for_home_creating" ] ) );
        $this->pushOrReplaceComponent( new WorldNotLoadedComponent( $eDetail[ "world_not_loaded" ] ) );

        $vaeDetail = $messages[ "validation_exception_detail" ];

        $this->pushOrReplaceComponent( new SpecialSymbolsDetectedComponent( $vaeDetail[ "special_symbols_detected" ] ) );
        $this->pushOrReplaceComponent( new NonLatinSymbolsDetectedComponent( $vaeDetail[ "non_latin_symbols_detected" ] ) );
        $this->pushOrReplaceComponent( new InvalidSymbolLengthDetectedComponent( $vaeDetail[ "invalid_symbol_length_detected" ] ) );
    }

    public function getStringComponent( string $compClass ): StringComponent {
        $comp = $this->tryGetComponent( $compClass );
        if( !$comp ) {
            throw new RuntimeException( "Could not find component " . $compClass . "." );
        }
        if( !( $comp instanceof StringComponent ) ) {
            throw new RuntimeException( "Component not of type StringComponent!" );
        }
        return $comp;
    }
}