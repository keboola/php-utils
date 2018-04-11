<?php

namespace Keboola\Utils;

class ToAsciiTest extends \PHPUnit_Framework_TestCase
{
    public function testToAscii()
    {
        $asciid = toAscii("~dlažební  %kostky_~");
        $this->assertEquals("~dlazebni  %kostky_~", $asciid);
    }
}
