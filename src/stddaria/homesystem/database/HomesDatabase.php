<?php

namespace stddaria\homesystem\database;

use pocketmine\math\Vector3;
use SQLite3;
use stddaria\homesystem\home\entity\HomeEntity;

class HomesDatabase {
    private SQLite3 $mSQLite;

    public function __construct( string $path ) {
        $this->mSQLite = new SQLite3( $path );
    }

    public function init(): void {
        $this->mSQLite->exec(
            "create table if not exists HomeRegistry("        .
                "playerName char(18) not null,"               .// 18, про за-пас.
                "homeName char(16) not null,"                 .// Максимальная длина названия точки дома.
                                                               // Координаты:
                "cX float not null,"                          .//   X.
                "cY float not null,"                          .//   Y.
                "cZ float not null,"                          .//   Z.
                "cWorldName char(256) not null"               .// Название мира, в котором была создана точка дома.
                                                               // Максимальная длина названия файла или папки: 256 байтов как в Windows, так и в Linux.
            ");"
        );
    }

    /**
     * @return HomeEntity[]
     */
    public function getHomes(): array {
        $sqResult = $this->mSQLite->query( "select * from HomeRegistry;" );
        if( !$sqResult ) {
            // Возврат пустого массива, потому
            // что не удалось выполнить выражение.
            return [];
        }
        $array = [];

        while( $sRes = $sqResult->fetchArray( SQLITE3_ASSOC ) ) {
            $playerName = $sRes[ "playerName" ];
            $homeName = $sRes[ "homeName" ];
            [ $x, $y, $z ] = [ (float)$sRes[ "cX" ], (float)$sRes[ "cY" ], (float)$sRes[ "cZ" ] ];
            $worldName = $sRes[ "cWorldName" ];

            $array[] = new HomeEntity( $playerName, $homeName, new Vector3( $x, $y, $z ), $worldName );
        }
        return $array;
    }

    public function deleteHome( string $pName, string $homeName ): bool {
        $stmt = $this->mSQLite->prepare( "delete from HomeRegistry where lower(playerName) = lower(:pName) and homeName = :hName;" );

        $stmt->bindValue( ":pName", $pName );
        $stmt->bindValue( ":hName", $homeName );

        return $stmt->execute() != false;
    }

    public function homeExists( string $pName, string $homeName ): bool {
        $stmt = $this->mSQLite->prepare( "select homeName from HomeRegistry where lower(playerName) = lower(:pName) and homeName = :hName;" );

        $stmt->bindValue( ":pName", $pName );
        $stmt->bindValue( ":hName", $homeName );

        $eResult = $stmt->execute();
        if( !$eResult ) {
            return false;
        }
        return $eResult->fetchArray() != false;
    }

    public function pushHome( HomeEntity $entity ): void {
        $stmt = $this->mSQLite->prepare( "insert into HomeRegistry values(:pName, :homeName, :x, :y, :z, :wName);" );

        $stmt->bindValue( ":pName", $entity->getPlayerName() );
        $stmt->bindValue( ":homeName", $entity->getHomeName() );

        $pos = $entity->getPos();

        $stmt->bindValue( ":x", $pos->getX() );
        $stmt->bindValue( ":y", $pos->getY() );
        $stmt->bindValue( ":z", $pos->getZ() );

        $stmt->bindValue( ":wName", $entity->getWorldName() );

        $stmt->execute();
    }
}