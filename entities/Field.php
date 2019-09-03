<?php

namespace seeBattle\entities;

use seeBattle\entities\ships\One_deck;

require __DIR__ . '/../vendor/autoload.php';

class Field
{
    const WIDTH = 500;
    const HEIGHT = 500;
    private $field = [];
    private $shipWidth;

    public function __construct()
    {
        $ship = new One_deck;
        $this->shipWidth = $ship->getWidth();
    }

    public function createBattleField()
    {
        for ($k = 0; $k < self::HEIGHT; $k += $this->shipWidth) { 
            $this->field[$k] = [];
            for ($i = 0;$i < self::WIDTH;$i += $this->shipWidth) {
                $this->field[$k][] = $i; 
            }
        }
        return $this->field;
    }
}