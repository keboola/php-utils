<?php

namespace Keboola\Utils;

class SanitizeNameTest extends \PHPUnit_Framework_TestCase
{

    public function testSanitizeName()
    {
        $sanitized = sanitizeName("_~dlažební  %_kostky_~");
        $this->assertEquals("dlazebni_kostky", $sanitized);
    }
}
