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

    private function randomDirection()
    {
        return rand(0, 1) === 0 ? 'horizontal' : 'vertical';
    }
    private function randomSign(int $num)
    {
        return rand(0, 1) === 0 ? $num + 1 : $num - 1;
    }
    private function randomFirstOrLast(array $hits, $coordinate)
    {
        $max = \collect($hits)->max($coordinate);
        $min = \collect($hits)->min($coordinate);
        return rand(0, 1) === 0 ? $min - 1 : $max + 1;
    }
    private function checkInAiMemory(array $point, ...$arr)
    {
        $arrFiltered = array_filter($arr);
        if (empty($arrFiltered)) return true;

        foreach ($arrFiltered as $innerArray) {
            foreach ($innerArray as $innerPoint) {
                if ($point['x'] === $innerPoint['x'] && $point['y'] === $innerPoint['y']) {
                    return false;
                }
            }
        }
        return true;
    }
    private function strToInt(...$array)
    {
        $arrFiltered = array_filter($array);
        if (empty($arrFiltered)) return false;
        $result = [];
        foreach ($array as $key => $innerArray) {
            $result[$key] = [];
            foreach ($innerArray as $key => $point) {
                $result[$key] = ['y' => (int)$point['y'], 'x' => (int)$point['x']];
            }
            $result[$key] = [ 'y' => (integer) $point['y'], 'x' => (integer) $point['x'] ];
        }
        return $result;
    }
    public function shoot()
    {
        $res = pg_query($this->connection, "SELECT * FROM hits;");
        $hits = $this->strToInt(pg_fetch_all($res));

        $res = pg_query($this->connection, "SELECT * FROM misses;");
        $misses = $this->strToInt(pg_fetch_all($res));

        $res = pg_query($this->connection, "SELECT * FROM ships;");
        $ships = $this->strToInt(pg_fetch_all($res));

        $res = pg_query($this->connection, "SELECT * FROM halo;");
        $halo = $this->strToInt(pg_fetch_all($res));

        

        $X = '';
        $Y = '';

        if ($hits) {
            if (count($hits) > 1) {
                $arrY = [];
                $arrX = [];
                foreach ($hits as $point) {
                    foreach ($point as $key => $value) {
                        if ($key === 'y') {
                            $arrY[] = $value;
                        }
                    }
                }
                $firstY = $arrY[0];
                $horizontal = \collect($arrY)->every(function($val) use($firstY){
                    return $val === $firstY;
                });

                foreach ($hits as $point) {
                    foreach ($point as $key => $value) {
                        if ($key === 'x') {
                            $arrX[] = $value;
                        }
                    }
                }
                $firstX = $arrX[0];
                $vertical = \collect($arrX)->every(function($val) use($firstX){
                    return $val === $firstX;
                });
                if ($horizontal) {
                    if ($hits[0]['x'] === 0) {
                        $X = $hits[count($hits) - 1]['x'] + 1;
                        $Y = $hits[0]['y'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    if ($hits[count($hits) - 1]['x'] === 9) {
                        $X = $hits[0]['x'] - 1;
                        $Y = $hits[0]['y'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    $X = $this->randomFirstOrLast($hits, 'x');
                    $Y = $hits[0]['y'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot();
                } else if ($vertical) {
                    if ($hits[0]['y'] === 0) {
                        $Y = $hits[count($hits) - 1]['y'] + 1;
                        $X = $hits[0]['x'];
                        if ($this->$this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    if ($hits[count($hits) - 1]['x'] === 9) {
                        $Y = $hits[0]['y'] - 1;
                        $X = $hits[0]['x'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    $Y = $this->randomFirstOrLast($hits, 'y');
                    $X = $hits[0]['x'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot();
                }
            } else {
                $direction = $this->randomDirection();
                if ($direction === 'horizontal') {
                    if ($hits[0]['x'] === 0) {
                        $X = 1;
                        $Y = $hits[0]['y'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    if ($hits[0]['x'] === 9) {
                        $X = 8;
                        $Y = $hits[0]['y'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    };
                    $X = $this->randomSign($hits[0]['x']);
                    $Y = $hits[0]['y'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot();
                } else if ($direction === 'vertical') {
                    if ($hits[0]['y'] === 0) {
                        $Y = 1;
                        $X = $hits[0]['x'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    if ($hits[0]['y'] === 9) {
                        $Y = 8;
                        $X = $hits[0]['x'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot();
                    }
                    $Y = $this->randomSign($hits[0]['y']);
                    $X = $hits[0]['x'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot();
                }
            }
        } else {
            $this->lastshoot = ['y' => rand(0, 9), 'x' => rand(0, 9)];

            if ($this->checkInAiMemory($this->lastshoot, $halo, $ships, $misses, $hits)) {
                return $this->lastshoot;
            }
            return $this->shoot;
        }
    }
    public function takeResponseFromUser($resOfLastShoot, $isShipNotSunk = [])
    {
        if (empty($resOfLastShoot)) {
            pg_query(
                $this->connection,
                "INSERT INTO misses
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