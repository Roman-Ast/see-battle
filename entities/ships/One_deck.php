<?php

namespace seeBattle\entities\ships;

use seeBattle\entities\Field;

class One_deck
{
  private $count_of_deck;
  private $width;
  private $height;

  public function __construct()
  {
      $this->count_of_deck = 1;
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
  public function createOneDeckShip()
  {
      return [
          'decks' => $this->getCountOfDeck(),
          'width' => $this->getWidth(),
          'height' => $this->getHeight()
      ];
  }
}

