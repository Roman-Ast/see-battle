<?php

namespace seeBattle\Tests;

require __DIR__ . '/../vendor/autoload.php';

use seeBattle\entities\ships\One_deck;
use seeBattle\entities\ships\Two_deck;
use seeBattle\entities\ships\Three_deck;
use seeBattle\entities\ships\Four_deck;
use seeBattle\entities\Field;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    public function testOneDeck()
    {
        $one_deck_ship = new One_deck();
        $this->assertEquals(1, $one_deck_ship->getCountOfDeck());
    }

    public function testTwoDeckShip()
    {
        $field = new Field();
        $two_deck_ship = new Two_deck();
        $createdShip = $two_deck_ship->createTwoDeckShip();
        $expected = [
            'decks' => 2,
            'width' => $field::WIDTH/10,
            'height' => $field::HEIGHT/10
        ];
        $this->assertEquals(2, $two_deck_ship->getCountOfDeck());
        $this->assertEquals($field::WIDTH/10, $two_deck_ship->getWidth());
        $this->assertEquals($field::HEIGHT/10, $two_deck_ship->getHeight());
        $this->assertEquals($expected, $createdShip);
    }

    public function testThreeDeckShip()
    {
        $field = new Field();
        $three_deck_ship = new Three_deck();
        $createdShip = $three_deck_ship->createThreeDeckShip();
        $expected = [
            'decks' => 3,
            'width' => $field::WIDTH/10,
            'height' => $field::HEIGHT/10
        ];
        $this->assertEquals(3, $three_deck_ship->getCountOfDeck());
        $this->assertEquals($field::WIDTH/10, $three_deck_ship->getWidth());
        $this->assertEquals($field::HEIGHT/10, $three_deck_ship->getHeight());
        $this->assertEquals($expected, $createdShip);
    }

    public function testFourDeckShip()
    {
        $field = new Field();
        $four_deck_ship = new Four_deck();
        $createdShip = $four_deck_ship->createFourDeckShip();
        $expected = [
            'decks' => 4,
            'width' => $field::WIDTH/10,
            'height' => $field::HEIGHT/10
        ];
        $this->assertEquals(4, $four_deck_ship->getCountOfDeck());
        $this->assertEquals($field::WIDTH/10, $four_deck_ship->getWidth());
        $this->assertEquals($field::HEIGHT/10, $four_deck_ship->getHeight());
        $this->assertEquals($expected, $createdShip);
    }
}