<?php

namespace seeBattle\entities;

use seeBattle\resources\ShipsCreator;

class Ai
{
    private $lastshoot;
    private $hits;
    private $halo;
    private $ships;
    private $misses;

    public function __construct() {}

    public function createShips()
    {
        $shipCreator = new ShipsCreator();
        return $shipCreator->createBattleShips();
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
    public function shoot($hitsArr, $missesArr, $shipsArr, $haloArr)
    {
        $hits = $this->strToInt($hitsArr);
        $misses = $this->strToInt($missesArr);
        $ships = $this->strToInt($shipsArr);
        $halo = $this->strToInt($haloArr);

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
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    if ($hits[count($hits) - 1]['x'] === 9) {
                        $X = $hits[0]['x'] - 1;
                        $Y = $hits[0]['y'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    $X = $this->randomFirstOrLast($hits, 'x');
                    $Y = $hits[0]['y'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                } else if ($vertical) {
                    if ($hits[0]['y'] === 0) {
                        $Y = $hits[count($hits) - 1]['y'] + 1;
                        $X = $hits[0]['x'];
                        if ($this->$this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    if ($hits[count($hits) - 1]['x'] === 9) {
                        $Y = $hits[0]['y'] - 1;
                        $X = $hits[0]['x'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    $Y = $this->randomFirstOrLast($hits, 'y');
                    $X = $hits[0]['x'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
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
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    if ($hits[0]['x'] === 9) {
                        $X = 8;
                        $Y = $hits[0]['y'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    };
                    $X = $this->randomSign($hits[0]['x']);
                    $Y = $hits[0]['y'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                } else if ($direction === 'vertical') {
                    if ($hits[0]['y'] === 0) {
                        $Y = 1;
                        $X = $hits[0]['x'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    if ($hits[0]['y'] === 9) {
                        $Y = 8;
                        $X = $hits[0]['x'];
                        if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                            $this->lastshoot = ['y' => $Y, 'x' => $X];
                            return $this->lastshoot;
                        }
                        return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                    }
                    $Y = $this->randomSign($hits[0]['y']);
                    $X = $hits[0]['x'];
                    if ($this->checkInAiMemory(['y' => $Y, 'x' => $X], $halo, $ships, $misses, $hits)) {
                        $this->lastshoot = ['y' => $Y, 'x' => $X];
                        return $this->lastshoot;
                    }
                    return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
                }
            }
        } else {
            $this->lastshoot = ['y' => rand(0, 9), 'x' => rand(0, 9)];

            if ($this->checkInAiMemory($this->lastshoot, $halo, $ships, $misses, $hits)) {
                return $this->lastshoot;
            }
            return $this->shoot($hitsArr, $missesArr, $shipsArr, $haloArr);
        }
    }

    public function fillHalo($hitsData)
    {
        $halo = $this->halo ?? [];

        $firstY = $hitsData[0]['y'];
        $horizontal = \collect($hitsData)->every(function($val) use($firstY){
            return $val['y'] === $firstY;
        });
        
        $coordsFirstPoint = false;
        $coordsLastPoint = false;

        if ($horizontal) {
            $coordsFirstPoint = array_reduce($hitsData, function($acc, $innerArray) {
                if ($innerArray['x'] < $acc['x']) {
                    $acc = $innerArray;
                    return $acc;
                }
                return $acc;
            }, ['y' => INF, 'x' => INF]);

            $coordsLastPoint = array_reduce($hitsData, function($acc, $innerArray) {
                if ($innerArray['x'] > $acc['x']) {
                    $acc = $innerArray;
                    return $acc;
                }
                return $acc;
            }, ['y' => -INF, 'x' => -INF]);

            for ($i = 0; $i < count($hitsData); $i++) { 
                array_push($halo, [ 'y' => $hitsData[$i]['y'] - 1, 'x' => $hitsData[$i]['x'] ]);  
            }
            for ($i = 0; $i < count($hitsData); $i++) { 
                array_push($halo, [ 'y' => $hitsData[$i]['y'] + 1, 'x' => $hitsData[$i]['x'] ]);  
            }

            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'], 'x' => $coordsFirstPoint['x'] - 1, ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] + 1, 'x' => $coordsFirstPoint['x'] - 1, ]);

            
            array_push($halo, [ 'y' => $coordsLastPoint['y'] - 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'], 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            
            $this->halo = array_slice($halo, 0);
            return $halo;
        } else {
            $coordsFirstPoint = array_reduce($hitsData, function($acc, $innerArray) {
                if ($innerArray['y'] < $acc['y']) {
                    $acc = $innerArray;
                    return $acc;
                }
                return $acc;
            }, ['y' => INF, 'x' => INF]);

            $coordsLastPoint = array_reduce($hitsData, function($acc, $innerArray) {
                if ($innerArray['y'] > $acc['y']) {
                    $acc = $innerArray;
                    return $acc;
                }
                return $acc;
            }, ['y' => -INF, 'x' => -INF]);

            for ($i = 0; $i < count($hitsData); $i++) { 
                array_push($halo, [ 'y' => $hitsData[$i]['y'], 'x' => $hitsData[$i]['x'] + 1 ]);  
            }
            for ($i = 0; $i < count($hitsData); $i++) { 
                array_push($halo, [ 'y' => $hitsData[$i]['y'], 'x' => $hitsData[$i]['x'] - 1 ]);  
            }

            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'], ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] + 1, ]);

            
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] - 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'], ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);

            $this->halo = array_slice($halo, 0);
            return $halo;
        }
    }
}