<?php

namespace seeBattle\Tests;

require __DIR__ . '/../vendor/autoload.php';

use seeBattle\src\Game;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testField()
    {
        $game = new Game();
        $aiField = $game->getAiField();
        $this->assertEquals(10, count($aiField['aiships']));
    }
}