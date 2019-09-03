<?php

namespace seeBattle\Tests;

require __DIR__ . '/../vendor/autoload.php';

use seeBattle\entities\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    public function testField()
    {
        $field = new Field();
        $battleField = $field->createBattleField();
        $this->assertEquals(10, count($battleField));
        $this->assertEquals(10, count($battleField[50]));
    }
}