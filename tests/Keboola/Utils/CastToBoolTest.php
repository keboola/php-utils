<?php

namespace Keboola\Utils;

use PHPUnit_Framework_TestCase;

class CastToBoolTest extends PHPUnit_Framework_TestCase
{
    public function testCast()
    {
        self::assertTrue(castToBool(true));
        self::assertTrue(castToBool('true'));
        self::assertTrue(castToBool('1'));
        self::assertTrue(castToBool(1));

        self::assertFalse(castToBool(false));
        self::assertFalse(castToBool('false'));
        self::assertFalse(castToBool('0'));
        self::assertFalse(castToBool(''));
        self::assertFalse(castToBool('avsavasdasd'));
        self::assertFalse(castToBool(0));
        self::assertFalse(castToBool(2));
        self::assertFalse(castToBool(1.0));
        self::assertFalse(castToBool(1.1));
        self::assertFalse(castToBool(132456));
        self::assertFalse(castToBool([]));
        self::assertFalse(castToBool(new \stdClass()));
        self::assertFalse(castToBool(null));
        self::assertFalse(castToBool(function () {
        }));
    }
}
