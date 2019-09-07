<?php

namespace seeBattle\user_entities;

require __DIR__ . '/../vendor/autoload.php';

class User_Field
{
    private $field = [];
    private $width;
    private $height;
    public $halo = [];

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
        $this->field = $this->createField();
    }

    public function createField()
    {
        for ($i = 0; $i < $this->height; $i++) { 
            $this->field[$i] = [];
            for ($k = 0; $k < $this->width; $k++) { 
                $this->field[$i][$k] = $k; 
            }
        }
        return $this->field;
    }
    public function validateShipCoords($shipCoords)
    {
        $coordsFromUser = $shipCoords['readyShipCoords'];
        $coordinates = [];
        $halo = [];
        foreach ($coordsFromUser as $point) {
            if (isset($this->field[$point['y']][$point['x']])) {
                $coordinates[] = $point;
            }
        }
        if (count($coordinates) === count($coordsFromUser)) {
            return $coordinates;
        }
        return false;

    }
}