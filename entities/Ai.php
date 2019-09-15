<?php

namespace seeBattle\entities;

class Ai
{
    private $connection;
    private $lastshoot;
    private $hits;
    private $halo;
    private $ships;
    private $misses;

    public function __construct()
    {
        $this->connection = pg_connect("host=localhost dbname=aimemory user=roman password=rimma");
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS misses(
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS hits(
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS ships(
                y integer,
                x integer
            );"
        );
        pg_query(
            $this->connection,
            "CREATE TABLE IF NOT EXISTS halo(
                y integer,
                x integer
            );"
        );
    }

    public function shoot()
    {
        $res = pg_query($this->connection, "SELECT * FROM hits;");
        $hits = pg_fetch_all($res);

        $res = pg_query($this->connection, "SELECT * FROM misses;");
        $misses = pg_fetch_all($res);

        if ($hits) {
            if (count($hits) > 1) {

            } else {
                if (!$misses) {
                    $Y = $hits[0]['y'] + 1;
                    $X = $hits[0]['y'];
                    return [ 'y' => $Y, 'x' => $X ];
                } else {
                    
                }
                
            }
        }






        

        $res = pg_query($this->connection, "SELECT * FROM ships;");
        $ships = pg_fetch_all($res);

        $res = pg_query($this->connection, "SELECT * FROM halo;");
        $halo = pg_fetch_all($res);

        $randX = rand(0, 9);
        $randY = rand(0, 9);

        $this->lastshoot = [ 'x' => 0, 'y' => 0 ];
        return $this->lastshoot;
    }
    public function takeResponseFromUser($resOfLastShoot, $isShipNotSunk = [])
    {
        if (empty($resOfLastShoot)) {
            pg_query(
                $this->connection,
                "INSERT INTO misses(y, x) 
                VALUES({$this->lastshoot['y']}, {$this->lastshoot['x']})"
            );
        } else {
            if(!$isShipNotSunk) {
                pg_query(
                    $this->connection,
                    "INSERT INTO hits(y, x) 
                    VALUES({$resOfLastShoot[0][0]['y']}, {$resOfLastShoot[0][0]['x']})"
                );
                $query = pg_query(
                    $this->connection,
                    "SELECT * FROM hits;"
                );
                $hitsData = pg_fetch_all($query);
                foreach ($hitsData as $point) {
                    pg_insert($this->connection, 'ships', $point);
                }
                $this->fillHalo($hitsData);
                pg_query($this->connection, "TRUNCATE hits;");
                return;
            }
            pg_query(
                $this->connection,
                "INSERT INTO hits(y, x) 
                VALUES({$resOfLastShoot[0][0]['y']}, {$resOfLastShoot[0][0]['x']})"
            );
        }
    }
    public function fillHalo($hitsData)
    {
        $halo = $this->halo ?? [];
        for ($i = 0; $i < count($hitsData); $i++) { 
            array_push($halo, [ 'y' => $hitsData[$i]['y'] - 1, 'x' => $hitsData[$i]['x'] ]);  
        }
        for ($i = 0; $i < count($hitsData); $i++) { 
            array_push($halo, [ 'y' => $hitsData[$i]['y'] + 1, 'x' => $hitsData[$i]['x'] ]);  
        }

        $coordsFirstPoint = $hitsData[0];
        array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
        array_push($halo, [ 'y' => $coordsFirstPoint['y'], 'x' => $coordsFirstPoint['x'] - 1, ]);
        array_push($halo, [ 'y' => $coordsFirstPoint['y'] + 1, 'x' => $coordsFirstPoint['x'] - 1, ]);

        $coordsLastPoint = $hitsData[count($hitsData) - 1];
        array_push($halo, [ 'y' => $coordsLastPoint['y'] - 1, 'x' => $coordsLastPoint['x'] + 1, ]);
        array_push($halo, [ 'y' => $coordsLastPoint['y'], 'x' => $coordsLastPoint['x'] + 1, ]);
        array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);
        $this->halo = array_slice($halo, 0);
        foreach ($halo as $point) {
            pg_insert($this->connection, 'halo', $point);
        }
    }
    
}