<?php

namespace Keboola\Utils;

use Keboola\Utils\Exception\JsonDecodeException,
	Keboola\Utils\Exception\Exception;
use Keboola\CsvTable\Table;
use Keboola\Temp\Temp;

class Utils {
	/**
	 * @brief PHP's json_decode which throws an exception on error
	 *
	 * @param string $json
	 * @param bool $assoc
	 * @param int $depth
	 * @param int $options
	 * @param bool $logJson: if true, the exception data will contain the JSON
	 * @return object|array
	 */
	public static function json_decode($json, $assoc = false, $depth = 512, $options = 0, $logJson = false)
	{
		$data = json_decode($json, $assoc, $depth, $options);
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				return $data;
			break;
			case JSON_ERROR_DEPTH:
				$error = 'Maximum stack depth exceeded';
			break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'Underflow or the modes mismatch';
			break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'Unexpected control character found';
			break;
			case JSON_ERROR_SYNTAX:
				$error = 'Syntax error, malformed JSON';
			break;
			case JSON_ERROR_UTF8:
				$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
			default:
				$error = 'Unknown error';
			break;
		}

		$e = new JsonDecodeException("JSON decode error: {$error}");
		if ($logJson) {
			$e->setData(array("json" => $json));
		}
		throw $e;
	}

	/**
	 * @brief Convert an array, object or a JSON string to associative array.
	 *
	 * @param mixed $data Data to convert
	 * @return array
	 */
	public static function to_assoc($data)
	{
		if (is_string($data)) { // TODO expand for XML
			return self::json_decode($data, true);
		} elseif (is_object($data) || is_array($data)) {
			$data = (array) $data;
			foreach($data as $key => $value) {
				if (is_object($value) || is_array($value)) {
					$data[$key] = self::to_assoc($value);
				}
			}
			return $data;
		} else {
			$type = gettype($data);
			throw new \Exception("Data to parse has to be either an array, object or a JSON string. {$type} provided.");
		}
	}

	/**
	 * @brief Create a CSV file in application's temp folder, and optionally set its header
	 *
	 * @param \Keboola\Temp\Temp $temp
	 * @param string $fileName File name Suffix
	 * @param array $header A header line to write into created file
	 * @return \Keboola\Csv\Table
	 * @deprecated Use \Keboola\CsvTable\Table::create($fileName, $header, $temp);
	 */
	public static function createCsv(Temp $temp, $fileName, array $header = array())
	{
		return Table::create($fileName, $header, $temp);
	}

	public static function formatDateTime($dateTime, $format = DATE_W3C, $timezone = null)
	{
		$dtzObj = $timezone ? new \DateTimeZone($timezone) : null;
		$dtObj = new \DateTime($dateTime, $dtzObj);
		return $dtObj->format($format);
	}

	public static function replaceDates($string, $tag = '%%', $format = DATE_W3C, $timezone = null)
	{
		return preg_replace_callback('/'.preg_quote($tag).'(.*?)'.preg_quote($tag).'/',
			function ($matches) use ($format, $timezone) {
				return self::formatDateTime($matches[1], $format, $timezone); // TODO format, timezone
			},
		$string);
	}

	public static function replaceDatesInArray($array, $tag = '%%', $format = DATE_W3C, $timezone = null)
	{
		array_walk_recursive($array, function(&$string, $key, $settings) {
			$string = self::replaceDates($string, $settings['tag'], $settings['format'], $settings['timezone']);
		}, ['tag' => $tag, 'format' => $format, 'timezone' => $timezone]);
		return $array;
	}

	/**
	 * @brief Inject query into an URL string
	 *
	 * @param string $url
	 * @param array $query Associative array containing query
	 * @return string Altered URL
	 */
	public static function buildUrl($url, array $query = null)
	{
		if (!empty($query)) {
			// Cleanup the input array to prevent empty Keys
			foreach($query as $k => $v) {
				if (empty($k)) {
					unset($query[$k]);
				}
			}

			# Parse the url to get a query string
			$parsed = parse_url($url);

			# If a query string is set, parse it into an associative array (..&key=value => array("key" => "value"))
			if (isset($parsed["query"]) && strlen($parsed["query"]) > 0) {
				$newQuery = array();
				$pairs = explode("&", $parsed["query"]);
				foreach($pairs as $pair) {
					list($key, $val) = explode("=", $pair);
					$newQuery[$key] = urldecode($val);
				}
				# Add/Replace parameters from $query
				$newQuery = array_replace($newQuery, $query);
			} else {
				$newQuery = $query;
			}
			$parsed["query"] = http_build_query($newQuery);

			# Rebuild the query back
			$url = self::http_build_url($parsed);
		}

		return $url;
	}

	/**
	 * @brief PECL http_build_query() replacement.
	 * Takes an array containing information about a parsed URL and rebuilds the URL from it.
	 * See http://php.net/manual/en/function.http-build-url.php
	 *
	 * @param array parse_url Array containing the parsed URL (i.e. result of http://ca1.php.net/manual/en/function.parse-url.php)
	 * @return string
	 **/
	public static function http_build_url(array $parse_url)
	{
		// Skip if the URL is relative
		if (!empty($parse_url["scheme"]) && !empty($parse_url["host"])) {
			// scheme - e.g. http
			$url = isset($parse_url["scheme"]) ? $parse_url["scheme"] : "http";
			$url .= "://";
			// user
			if (isset($parse_url["user"])) {
				$url .= $parse_url["user"];
				// pass
				$url .= isset($parse_url["pass"]) ? ":{$parse_url["pass"]}" : "";
				$url .= "@";
			}
			// host
			$url .= isset($parse_url["host"]) ? $parse_url["host"] : "";
			// port
			$url .= isset($parse_url["port"]) ? ":{$parse_url["port"]}" : "";
		} else {
			$url = "";
		}

		// path
		$url .= isset($parse_url["path"]) ? $parse_url["path"] : "";
		// query - after the question mark ?
		$url .= isset($parse_url["query"]) ? "?{$parse_url["query"]}" : "";
		// fragment - after the hashmark #
		$url .= isset($parse_url["fragment"]) ? "#{$parse_url["fragment"]}" : "";
		return $url;
	}

	public static function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}


	public static function camelize($string, $ucfirst = false)
	{
		$string = str_replace(["_", "-"], " ", $string);
		$string = ucwords($string);
		$string = str_replace(" ", "", $string);
		return $ucfirst ? $string : lcfirst($string);
	}

	/**
	 * Returns associative array or string of data stored in $path in the $data array
	 *
	 * @param string $path path/to/interesting/data
	 * @param array|object $data The object containing data
	 * @return mixed
	 */
	public static function getDataFromPath($path, $data, $separator = "/", $ignoreEmpty = true)
	{
		if (!empty($path) && $path != $separator) {
			// TODO possibly add functions in the $path? Such as path/to/join(data) would do a join on that data..likely belongs to EX/(sub)Parser/TR
			$str = explode($separator, $path);
			// Step down into the $data object, iterate until the desired path is reached (if not empty)
			foreach($str as $key) {
				if (is_object($data) && property_exists($data, $key)) {
					$data = $data->{$key};
				} elseif (is_array($data) && isset($data[$key])) {
					$data = $data[$key];
				} else {
					if ($ignoreEmpty == true) { // return if empty and ignore == true
						$data = "";
						break;
					} else {
						throw new Exception("Error parsing data. {$path} not found.");
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Check if a string is a valid date(time)
	 *
	 * @link http://www.pontikis.net/tip/?id=21
	 *
	 * @param string $dateStr
	 * @param string $dateFormat
	 * @param string $timezone (If timezone is invalid, php will throw an exception)
	 * @return bool
	 */
	public static function isValidDateTimeString($dateStr, $dateFormat, $timezone = null)
	{
		if ($timezone) {
			$date = \DateTime::createFromFormat(
				$dateFormat,
				$dateStr,
				new \DateTimeZone($timezone)
			);
		} else {
			$date = \DateTime::createFromFormat($dateFormat, $dateStr);
		}

		return $date && \DateTime::getLastErrors()["warning_count"] == 0 && \DateTime::getLastErrors()["error_count"] == 0;
	}

}
