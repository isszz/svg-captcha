<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace isszz\captcha\font\lib\truetype;

/**
 * TrueType font file header.
 *
 * @package php-font-lib
 */
class Header extends \isszz\captcha\font\lib\Header
{
	protected $def = [
		"format"        => self::uint32,
		"numTables"     => self::uint16,
		"searchRange"   => self::uint16,
		"entrySelector" => self::uint16,
		"rangeShift"    => self::uint16,
	];

	public function parse() {
		parent::parse();

		$format	= $this->data["format"];
		$this->data["formatText"] = $this->convertUInt32ToStr($format);
	}
}