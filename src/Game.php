<?php

namespace seeBattle\src;

require __DIR__ . '/../vendor/autoload.php';

use seeBattle\entities\ships\Four_deck;
use seeBattle\entities\ships\Three_deck;
use seeBattle\entities\ships\Two_deck;
use seeBattle\entities\ships\One_deck;
use seeBattle\entities\Field;

/*class Game
{
    public $field;
    public $halo = [];

    public function __construct()
    {
        $fieldEntity = new Field(10, 10);
        $this->field = $fieldEntity->createField();
    }

    public function Vector()
    {
        return rand(1, 2) === 1 ? 'vertical' : 'horizontal';   
    }
    public function createShipCoords(int $countOfDecks)
    {
        $field = array_slice($this->field, 0);
        $x = rand(0, 9);
        $y = rand(0, 9);

        $coordinates = [];
        $halo = [];
        $firstPoint = [];

        if (isset($field[$y][$x])) {
            $firstPoint['y']= array_keys($field)[$y];
            $firstPoint['x']= $field[$y][$x];
        
            $coordinates[] = $firstPoint;
        } else {
            return $this->createShipCoords($countOfDecks);
        }
        
        if ($this->Vector() === 'horizontal') {
            $yAuto = $firstPoint['y'];
            $xAuto = 0;
            for ($i = 1; $i < $countOfDecks; $i++) { 
                if (isset($field[$firstPoint['y']][$firstPoint['x'] + $i])) {
                    $xAuto = $field[$firstPoint['y']][$firstPoint['x'] + $i];
                } else {
                    return $this->createShipCoords($countOfDecks);
                }
                $coordinates[] = [ 'y' => $yAuto, 'x' => $xAuto ];
            }
            for ($i = 0; $i < count($coordinates); $i++) { 
                array_push($halo, [ 'y' => $coordinates[$i]['y'] - 1, 'x' => $coordinates[$i]['x'] ]);  
            }
            for ($i = 0; $i < count($coordinates); $i++) { 
                array_push($halo, [ 'y' => $coordinates[$i]['y'] + 1, 'x' => $coordinates[$i]['x'] ]);  
            }

            $coordsFirstPoint = $coordinates[0];
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'], 'x' => $coordsFirstPoint['x'] - 1, ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] + 1, 'x' => $coordsFirstPoint['x'] - 1, ]);

            $coordsLastPoint = $coordinates[count($coordinates) - 1];
            array_push($halo, [ 'y' => $coordsLastPoint['y'] - 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'], 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($this->halo, $halo);
        }
        else {
            for ($i = 1; $i < $countOfDecks; $i++) { 
                $xAuto = $firstPoint['x'];
                if (isset($field[$firstPoint['y'] + $i][$firstPoint['x']])) {
                    $yAuto = $firstPoint['y'] + $i;
                } else {
                    return $this->createShipCoords($countOfDecks);
                }
                $coordinates[] = [ 'y' => $yAuto, 'x' => $xAuto ];
            }
            for ($i = 0; $i < count($coordinates); $i++) { 
                array_push($halo, [ 'y' => $coordinates[$i]['y'], 'x' => $coordinates[$i]['x'] - 1 ]);  
            }
            for ($i = 0; $i < count($coordinates); $i++) { 
                array_push($halo, [ 'y' => $coordinates[$i]['y'], 'x' => $coordinates[$i]['x'] + 1 ]);  
            }

            $coordsFirstPoint = $coordinates[0];
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] - 1 ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'], ]);
            array_push($halo, [ 'y' => $coordsFirstPoint['y'] - 1, 'x' => $coordsFirstPoint['x'] + 1, ]);

            $coordsLastPoint = $coordinates[count($coordinates) - 1];
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] - 1, ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'], ]);
            array_push($halo, [ 'y' => $coordsLastPoint['y'] + 1, 'x' => $coordsLastPoint['x'] + 1, ]);
            array_push($this->halo, $halo);
        }

        foreach ($coordinates as $point) {
            unset($field[$point['y']][$point['x']]);
        }

        foreach ($halo as $point) {
            unset($field[$point['y']][$point['x']]);
        }

        $this->field = array_slice($field, 0);
        return $coordinates;
    }
    public function createBattleField()
    {
        $fourDeckShip = new Four_deck();
        $threeDeck1 = new Three_Deck();
        $threeDeck2 = new Three_Deck();
        $twoDeck1 = new Two_Deck();
        $twoDeck2 = new Two_Deck();
        $twoDeck3 = new Two_Deck();
        $oneDeck1 = new One_Deck();
        $oneDeck2 = new One_Deck();
        $oneDeck3 = new One_Deck();
        $oneDeck4 = new One_Deck();

        return [
            'fourDeck' => $this->createShipCoords($fourDeckShip->getCountOfDeck()),
            'threeDeck1' => $this->createShipCoords($threeDeck1->getCountOfDeck()),
            'threeDeck2' => $this->createShipCoords($threeDeck2->getCountOfDeck()),
            'twoDeck1' => $this->createShipCoords($twoDeck1->getCountOfDeck()),
            'twoDeck2' => $this->createShipCoords($twoDeck2->getCountOfDeck()),
            'twoDeck3' => $this->createShipCoords($twoDeck3->getCountOfDeck()),
            'oneDeck1' => $this->createShipCoords($oneDeck1->getCountOfDeck()),
            'oneDeck2' => $this->createShipCoords($oneDeck2->getCountOfDeck()),
            'oneDeck3' => $this->createShipCoords($oneDeck3->getCountOfDeck()),
            'oneDeck4' => $this->createShipCoords($oneDeck4->getCountOfDeck()),
        ];
    }
    public function getField()
    {
        return $this->field;
    }
    public function getHalo()
    {
        return $this->halo;
    }
}*/