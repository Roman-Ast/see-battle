<?php

namespace seeBattle\src;

require __DIR__ . '/../vendor/autoload.php';

use seeBattle\resources\Validator;
use seeBattle\entities\Field;
use seeBattle\entities\Ai;

class Game
{
    private $_aiconn;
    private $_userconn;
    private $_aimemory;
    private $_aiships;
    private $_userships;
    private $_userShoots;

    public function __construct()
    {
        $this->_userconn = pg_connect(
            "host=localhost dbname=userships user=roman password=rimma"
        );
        $this->_aiconn = pg_connect(
            "host=localhost dbname=aiships user=roman password=rimma"
        );
        $this->_aimemory = pg_connect(
            "host=localhost dbname=aimemory user=roman password=rimma"
        );
        $this->_userShoots = pg_connect(
            "host=localhost dbname=usershoots user=roman password=rimma"
        );
        pg_query(
            $this->_userShoots, 
            "CREATE TABLE IF NOT EXISTS usershoots(
                y integer,
                x integer
            );"
        );
    }

    public function getAiField()
    {
        $ai = new Ai();
        $aiShips = $ai->createShips();
        

        foreach ($aiShips as $shipname => $points) {
            pg_query(
                $this->_aiconn, 
                "CREATE TABLE IF NOT EXISTS {$shipname}(
                    y integer,
                    x integer
                );"
            );
        } 
        foreach ($aiShips as $shipname => $points) {
            pg_query($this->_aiconn, "TRUNCATE {$shipname}");
            foreach ($points as $key => $point) {
                pg_insert($this->_aiconn, strtolower($shipname), $point);
            }
        }
        $aiShipsFromDB = [];

        foreach ($aiShips as $shipname => $points) {
            $lowershipname = strtolower($shipname);
            $result = pg_query($this->_aiconn, "SELECT * FROM {$lowershipname}");
            $aiShipsFromDB[$shipname] = pg_fetch_all($result);
        }

        foreach ($aiShipsFromDB as $shipname => $points) {
            $lowershipname = strtolower($shipname);
            pg_query($this->_userconn, "TRUNCATE {$lowershipname}");
        }

        pg_query($this->_aimemory, "TRUNCATE hits");
        pg_query($this->_aimemory, "TRUNCATE misses");
        pg_query($this->_aimemory, "TRUNCATE ships");
        pg_query($this->_aimemory, "TRUNCATE halo");
        pg_query($this->_userShoots, "TRUNCATE usershoots");

        return ['aiships' => $aiShipsFromDB];
    }

    public function checkUserField($shipCoords)
    {
        $validator = new Validator();
        $validatedUserField = $validator->validate($shipCoords);

        if (isset($validatedUserField['error'])) {
            return $validatedUserField;
        }

        foreach ($shipCoords as $shipname => $points) {
            pg_query(
                $this->_userconn, 
                "CREATE TABLE IF NOT EXISTS {$shipname}(
                    y integer,
                    x integer
                );"
            );
        }
        foreach ($shipCoords as $shipname => $points) {
            pg_query($this->_userconn, "TRUNCATE {$shipname}");
            foreach ($points as $key => $point) {
                pg_insert($this->_userconn, strtolower($shipname), $point);
            }
        }

        $userShips = [];
        foreach ($shipCoords as $shipname => $points) {
            $lowershipname = strtolower($shipname);
            $result = pg_query($this->_userconn, "SELECT * FROM {$lowershipname}");
            $userShips[$shipname] = pg_fetch_all($result);
        }
        return $userShips;
    }

    public function userStep($targetCoords)
    {
        $miss = '';
        $isShipAfloat = '';
        $sunkedShip = '';

        $record = pg_select($this->_userShoots, 'usershoots', $targetCoords);
        if ($record) {
            return false;
        }
        pg_insert($this->_userShoots, 'usershoots', $targetCoords);

        $result = pg_query(
            $this->_aiconn,
            "select pg_tables.tablename from pg_tables where schemaname='public';"
        );
        $shipsNames = pg_fetch_all($result);

        $deletedItem = '';
        foreach ($shipsNames as $shipName) {
            $result = pg_query(
                $this->_aiconn, 
                "DELETE FROM {$shipName['tablename']} 
                WHERE y = {$targetCoords['y']} 
                AND x = {$targetCoords['x']} 
                RETURNING *;"
            );
            $deletedItem = pg_fetch_all($result);
            if ($deletedItem) {
                $temp = pg_query(
                    $this->_aiconn,
                    "SELECT * FROM {$shipName['tablename']};"
                );
                $isShipAfloat = pg_fetch_all($temp);
                if (!$isShipAfloat) {
                    $sunkedShip = $shipName['tablename'];
                }
                break;
            }
        }
        
        if (!$deletedItem) {
            $miss = $targetCoords;
        }

        $tablesNormalized = [];
        foreach ($shipsNames as $ship) {
            foreach ($ship as $key => $value) {
                $tablesNormalized[] = $value;
            }
        }
        $aishipsUpdated = [];

        foreach ($shipsNames as $shipName) {
            $lowershipname = strtolower($shipName['tablename']);
            $result = pg_query(
                $this->_aiconn,
                "SELECT * FROM {$lowershipname}"
            );
            $aishipsUpdated[$lowershipname] = pg_fetch_all($result);
        }

        return [
            'aishipsUpdated' => $aishipsUpdated,
            'deletedItem' => $deletedItem,
            'miss' => $miss,
            'isShipAfloat' => $isShipAfloat,
            'sunkedShip' => $sunkedShip,
            'isWinner' => $this->isWinner('user', $aishipsUpdated)
        ];
    }

    public function aiStep()
    {
        pg_query(
            $this->_aimemory,
            "CREATE TABLE IF NOT EXISTS misses(
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->_aimemory,
            "CREATE TABLE IF NOT EXISTS hits(
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->_aimemory,
            "CREATE TABLE IF NOT EXISTS ships(
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->_aimemory,
            "CREATE TABLE IF NOT EXISTS halo(
                y integer,
                x integer
            );"
        );

        $res = pg_query($this->_aimemory, "SELECT * FROM hits;");
        $hits = pg_fetch_all($res);

        $res = pg_query($this->_aimemory, "SELECT * FROM misses;");
        $misses = pg_fetch_all($res);

        $res = pg_query($this->_aimemory, "SELECT * FROM ships;");
        $ships = pg_fetch_all($res);

        $res = pg_query($this->_aimemory, "SELECT * FROM halo;");
        $halo = pg_fetch_all($res);

        $emptytablename = '';
        $isShipAfloat = '';

        $Ai = new Ai();
        $shootingCoordsFromAi = $Ai->shoot($hits, $misses, $ships, $halo);
        
        //выбираем все таблицы из базы с кораблями пользователя
        $result = pg_query(
            $this->_userconn, 
            "SELECT table_name FROM information_schema.tables 
            WHERE table_schema = 'public';"
        );
        //нормализация данных для обработки
        $tables = pg_fetch_all($result);

        $tablesNormalized = [];
        foreach (array_values($tables) as $value) {
            $tablesNormalized[] = $value['table_name'];
        }
        //проверка результата выстрела (попадание или промах)
        $resultOfAiShooting = [];
        foreach ($tablesNormalized as $tableName) {
            $responseFromDB = pg_query(
                $this->_userconn, 
                "SELECT * FROM {$tableName} 
                WHERE x = {$shootingCoordsFromAi['x']} AND y = {$shootingCoordsFromAi['y']};"
            );
            if (pg_fetch_all($responseFromDB)) {
                $emptytablename = $tableName;
                $resultOfAiShooting[] = pg_fetch_all($responseFromDB);
                pg_query(
                    $this->_userconn,
                    "DELETE FROM {$tableName} 
                    WHERE y = {$shootingCoordsFromAi['y']} 
                    AND x = {$shootingCoordsFromAi['x']};"
                );
                break;
            };
        }
        //отправка компьютеру ответа с результатом выстрела (попадание или промах)
        if (!empty($resultOfAiShooting)) {
            $res = pg_query(
                $this->_userconn,
                "SELECT * FROM {$emptytablename};"
            );
            $isShipAfloat = pg_fetch_all($res);
            $this->takeResponseFromUser($resultOfAiShooting, $shootingCoordsFromAi, $isShipAfloat);
        } else {
            $this->takeResponseFromUser($resultOfAiShooting, $shootingCoordsFromAi);
        }
        //оновление данных по кораблям пользователя с учетом попаданий
        foreach ($tablesNormalized as $tableName) {
            $lowershipname = strtolower($tableName);
            $temp = pg_query(
                $this->_userconn,
                "SELECT * FROM {$tableName}"
            );
            $resultingArray[$lowershipname] = pg_fetch_all($temp);
        }

        //$this->_userships = $resultingArray;

        return [
            'resultArr' => $resultingArray,
            'resOfShooting' => 
                empty($resultOfAiShooting) ? $shootingCoordsFromAi : $resultOfAiShooting,
            'isShipAfloat' => $isShipAfloat,
            'sunkedShip' => $emptytablename,
            'isWinner' => $this->isWinner('ai', $resultingArray)
        ];
    }

    public function takeResponseFromUser($resOfLastShoot, $lastshoot, $isShipNotSunk = [])
    {
        $Ai = new Ai();

        if (empty($resOfLastShoot)) {
            pg_query(
                $this->_aimemory,
                "INSERT INTO misses
                VALUES({$lastshoot['y']}, {$lastshoot['x']})"
            );
        } else {
            if(!$isShipNotSunk) {
                pg_query(
                    $this->_aimemory,
                    "INSERT INTO hits(y, x) 
                    VALUES({$resOfLastShoot[0][0]['y']}, {$resOfLastShoot[0][0]['x']})"
                );
                $query = pg_query(
                    $this->_aimemory,
                    "SELECT * FROM hits;"
                );
                $hitsData = pg_fetch_all($query);
                foreach ($hitsData as $point) {
                    pg_insert($this->_aimemory, 'ships', $point);
                }
                $halo = $Ai->fillHalo($hitsData);

                foreach ($halo as $point) {
                    pg_insert($this->_aimemory, 'halo', $point);
                }

                pg_query($this->_aimemory, "TRUNCATE hits;");
                return;
            }
            pg_query(
                $this->_aimemory,
                "INSERT INTO hits(y, x) 
                VALUES({$resOfLastShoot[0][0]['y']}, {$resOfLastShoot[0][0]['x']})"
            );
        }
    }

    public function isWinner($player, $playerShips)
    {
        $restOfShips = [];

        if ($player === 'ai') {
            $result = pg_query(
                $this->_aiconn, 
                "SELECT table_name FROM information_schema.tables 
                WHERE table_schema = 'public';"
            );
            
            $tables = pg_fetch_all($result);

            $tablesNormalized = [];
            foreach (array_values($tables) as $value) {
                $tablesNormalized[] = $value['table_name'];
            }

            foreach ($tablesNormalized as $tableName) {
                $lowershipname = strtolower($tableName);
                $temp = pg_query(
                    $this->_aiconn,
                    "SELECT * FROM {$tableName}"
                );
                $restOfShips[$lowershipname] = pg_fetch_all($temp);
            }
        }
        foreach ($playerShips as $ship) {
            if ($ship) {
                return false;
            }
        }

        return ['restOfShips' => $restOfShips];
    }
}