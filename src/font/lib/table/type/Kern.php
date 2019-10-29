<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace isszz\captcha\font\lib\table\type;
use isszz\captcha\font\lib\table\Table;

/**
 * `kern` font table.
 *
 * @package php-font-lib
 */
class Kern extends Table
{
	protected function _parse()
	{
		$font = $this->getFont();

		$data = $font->unpack([
			"version"         => self::uint16,
			"nTables"         => self::uint16,

			// only the first subtable will be parsed
			"subtableVersion" => self::uint16,
			"length"          => self::uint16,
			"coverage"        => self::uint16,
		]);

		$data["format"] = ($data["coverage"] >> 8);

		$subtable = [];

		switch ($data["format"]) {
			case 0:
				$subtable = $font->unpack([
					"nPairs"        => self::uint16,
					"searchRange"   => self::uint16,
					"entrySelector" => self::uint16,
					"rangeShift"    => self::uint16,
				]);

				$pairs = [];
				$tree  = [];

				$values = $font->readUInt16Many($subtable["nPairs"] * 3);
				for ($i = 0, $idx = 0; $i < $subtable["nPairs"]; $i++) {
					$left  = $values[$idx++];
					$right = $values[$idx++];
					$value = $values[$idx++];

					if ($value >= 0x8000) {
						$value -= 0x10000;
					}

					$pairs[] = [
						"left"  => $left,
						"right" => $right,
						"value" => $value,
					];

					$tree[$left][$right] = $value;
				}

				//$subtable["pairs"] = $pairs;
				$subtable["tree"] = $tree;
				break;

			case 1:
			case 2:
			case 3:
				break;
		}

		$data["subtable"] = $subtable;

		$this->data = $data;
	}
}