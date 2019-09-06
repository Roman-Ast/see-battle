<?php

namespace seeBattle\entities;

use seeBattle\entities\ships\One_deck;

require __DIR__ . '/../vendor/autoload.php';

class Field
{
    private $field = [];
    private $width;
    private $height;

    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
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
}