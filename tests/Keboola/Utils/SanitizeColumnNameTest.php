<?php

namespace Keboola\Utils;

class SanitizeColumnNameTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider namesToSanitize
     *
     **/
    public function testSanitizeColumnName($nameToSanitize, $sanitizedName)
    {
        $sanitized = sanitizeColumnName($nameToSanitize);
        $this->assertEquals($sanitizedName, $sanitized);
    }

    private function namesToSanitize()
    {
        return [
            [
                "_~dlažební  %_kostky_~",
                "dlazebni_kostky"
            ]
        ];
    }
}
