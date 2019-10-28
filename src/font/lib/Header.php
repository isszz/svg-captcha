<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */
namespace isszz\captcha\font\lib;

use isszz\captcha\font\lib\TrueType\File;

/**
 * Font header container.
 *
 * @package php-font-lib
 */
abstract class Header extends BinaryStream
{
	/**
	 * @var File
	 */
	protected $font;
	protected $def = [];

	public $data;

	public function __construct(File $font)
	{
		$this->font = $font;
	}

	public function encode()
	{
		return $this->font->pack($this->def, $this->data);
	}

	public function parse()
	{
		$this->data = $this->font->unpack($this->def);
	}
}