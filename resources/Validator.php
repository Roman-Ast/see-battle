<?php

namespace seeBattle\resources;

require __DIR__ . '/../vendor/autoload.php';

class Validator
{
    private $halo;
    private $field;

    public function __construct()
    {
        $this->halo = [];
        $this->field = $this->createField(10, 10);
    }

    public function createField(int $width, int $height)
        {
            for ($i = 0; $i < $height; $i++) { 
                $this->field[$i] = [];
                for ($k = 0; $k < $width; $k++) { 
                    $this->field[$i][$k] = $k; 
                }
            }
            return $this->field;
        }
    public function validate($shipCoords)
    {
            $onlyPoints = [];
            $values = array_values($shipCoords);

            for ($i=0; $i < count($shipCoords); $i++) { 
                $onlyPoints[$i] = $values[$i];
            }
            foreach ($onlyPoints as $ship) {
                if (isset($this->validateShipCoords($ship)['error'])) {
                    return $this->validateShipCoords($ship);
                }
            }
            return $shipCoords;
    }
    public function validateShipCoords($shipCoords)
    {
        $coordsFromUser = $shipCoords;

        $arrY = [];
        foreach ($coordsFromUser as $point) {
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

        $arrX = [];
        foreach ($coordsFromUser as $point) {
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
            $firstX = $coordsFromUser[0]['x'];
            for ($i = 0; $i < count($coordsFromUser)-1; $i++) { 
                if ($coordsFromUser[$i+1]['x'] - $coordsFromUser[$i]['x'] !== 1) {
                    return ['error' => 'error', 'coords' => $shipCoords];
                }
            }

            foreach ($coordsFromUser as $point) {
                foreach ($point as $key => $value) {
                    if (!isset($this->field[$point['y']][$point['x']])) {
                        return ['error' => 'error', 'coords' => $shipCoords];
                    }
                }
            }

            $halo = $this->halo;
            for ($i = 0; $i < count($coordsFromUser); $i++) { 
                array_push($halo, [ 'y' => $coordsFromUser[$i]['y'] - 1, 'x' => $coordsFromUser[$i]['x'] ]);  
            }
            for ($i = 0; $i < count($coordsFromUser); $i++) { 
                array_push($halo, [ 'y' => $coordsFromUser[$i]['y'] + 1, 'x' => $coordsFromUser[$i]['x'] ]);  
            }

            $coordsFirstPoint = $coordsFromUser[0];
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'], 'x' => $coordsFirstPoint['x'] - 1, ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] + 1, 'x' => $coordsFirstPoint['x'] - 1, ]);

            $coordsLastPoint = $coordsFromUser[count($coordsFromUser) - 1];
            array_push($halo, [ 'y' => $coordsLastPoint['y'] - 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'], 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($this->halo, $halo);

            foreach ($coordsFromUser as $point) {
                unset($this->field[$point['y']][$point['x']]);
            }
    
            foreach ($halo as $point) {
                unset($this->field[$point['y']][$point['x']]);
            }

            $this->halo = array_slice($halo, 0);
            return $shipCoords;

        } else if ($vertical) {
            $firstX = $coordsFromUser[0]['y'];
            for ($i = 0; $i < count($coordsFromUser)-1; $i++) { 
                if ($coordsFromUser[$i+1]['y'] - $coordsFromUser[$i]['y'] !== 1) {
                    return ['error' => 'error', 'coords' => $shipCoords];
                }
            }

            foreach ($coordsFromUser as $point) {
                foreach ($point as $key => $value) {
                    if (!isset($this->field[$point['y']][$point['x']])) {
                        return ['error' => 'error', 'coords' => $shipCoords];
                    }
                }
            }

            $halo = $this->halo;
            for ($i = 0; $i < count($coordsFromUser); $i++) { 
                array_push($halo, [ 'y' => $coordsFromUser[$i]['y'], 'x' => $coordsFromUser[$i]['x'] - 1 ]);  
            }
            for ($i = 0; $i < count($coordsFromUser); $i++) { 
                array_push($halo, [ 'y' => $coordsFromUser[$i]['y'], 'x' => $coordsFromUser[$i]['x'] + 1 ]);  
            }

            $coordsFirstPoint = $coordsFromUser[0];
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'], ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] + 1, ]);

            $coordsLastPoint = $coordsFromUser[count($coordsFromUser) - 1];
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] - 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'], ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($this->halo, $halo);

            foreach ($coordsFromUser as $point) {
                unset($this->field[$point['y']][$point['x']]);
            }
    
            foreach ($halo as $point) {
                unset($this->field[$point['y']][$point['x']]);
            }

            $this->halo = array_slice($halo, 0);
            return $shipCoords;
        }
        return ['error' => 'error', 'coords' => $shipCoords];
    }
    public function getHalo()
    {
        return $this->halo;
    }
    public function getField()
    {
        return $this->field;
    }
}