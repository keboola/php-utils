<?php

use Keboola\Utils\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase {

	public function testFormatDateTime()
	{
		$this->assertEquals(Utils::formatDateTime("15.2.2014 16:00", "Y-m-d H:i"), "2014-02-15 16:00");
		$this->assertEquals(Utils::formatDateTime("now"), date(DATE_W3C));
	}

	public function testReplaceDatesInArray()
	{
		$array = [
			'some ~~yesterday~~ thing',
			'arr' => [
				'key' => 'something deeper from ~~-6 days~~'
			],
			'another' => [
				'oh hi' => [
					'deep as adele! ~~20071231~~'
				]
			]
		];

		$parsed = [
			'some ' . Utils::formatDateTime('yesterday') . ' thing',
			'arr' => [
				'key' => 'something deeper from ' . Utils::formatDateTime('-6 days'),
			],
			'another' => [
				'oh hi' => [
				0 => 'deep as adele! 2007-12-31T00:00:00+00:00',
				],
			],
		];

		$this->assertEquals(Utils::replaceDatesInArray($array, '~~'), $parsed);
	}

	public function testReplaceDates()
	{
		$this->assertEquals(Utils::replaceDates("@@now@@", "@@"), date(DATE_W3C));
		$this->assertEquals(Utils::replaceDates("%%now%%"), date(DATE_W3C));
	}

	public function testGetDataFromPath() {
		$data = array(
			"p" => array(
				"a" => array(
					"t" => array(
						"h" => "Hello world!"
					)
				)
			)
		);
		$slash = Utils::getDataFromPath("p/a/t/h", $data);
		$this->assertEquals($slash, $data["p"]["a"]["t"]["h"]);
		$dot = Utils::getDataFromPath("p.a.t.h", $data, ".");
		$this->assertEquals($dot, $data["p"]["a"]["t"]["h"]);
		$null = Utils::getDataFromPath("p/a/t/g", $data);
		$this->assertEquals($null, "");
	}

	public function testIsValidDateTimeString()
	{
		$this->assertEquals(Utils::isValidDateTimeString("Fri, 31 Dec 1999 23:59:59 GMT", DATE_RFC1123), true);
		$this->assertEquals(Utils::isValidDateTimeString("Fri, 31 Dec 1999 23:59:59 +0000", DATE_RFC1123), true);
		$this->assertEquals(Utils::isValidDateTimeString("2005-08-15T15:52:01+00:00", DATE_W3C), true);
	}
}
