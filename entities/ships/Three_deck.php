<?php

namespace seeBattle\entities\ships;

use seeBattle\src\Game;

class Three_deck
{
  private $count_of_deck;
  private $width;
  private $height;

  public function __construct()
  {
      $game = new Game();
      $this->count_of_deck = 3;
      $this->width = 50;
      $this->height = 50;
  }

  public function getCountOfDeck()
  {
      return $this->count_of_deck;
  }
  public function getWidth()
  {
      return $this->width;
  }
  public function getHeight()
  {
      return $this->height;
  }
  public function createThreeDeckShip()
  {
      return [
          'decks' => $this->getCountOfDeck(),
          'width' => $this->getWidth(),
          'height' => $this->getHeight()
      ];
  }
}

