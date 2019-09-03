<?php

namespace seeBattle\entities\ships;

class Four_deck
{
  private $count_of_deck;
  private $width;
  private $height;

  public function __construct()
  {
      $this->count_of_deck = 4;
      $this->width = 10;
      $this->height = 10;
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
  public function createFourDeckShip()
  {
      return [
          'decks' => $this->getCountOfDeck(),
          'width' => $this->getWidth(),
          'height' => $this->getHeight()
      ];
  }
}

