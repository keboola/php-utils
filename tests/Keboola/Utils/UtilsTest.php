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
}
