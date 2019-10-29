<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @version $Id: Font_Table_glyf.php 46 2012-04-02 20:22:38Z fabien.menager $
 */
namespace isszz\captcha\font\lib\glyph;

use isszz\captcha\font\lib\table\type\Glyf;
use isszz\captcha\font\lib\truetype\File;
use isszz\captcha\font\lib\BinaryStream;

/**
 * `glyf` font table.
 *
 * @package php-font-lib
 */
class Outline extends BinaryStream
{
	/**
	 * @var \isszz\captcha\font\lib\table\type\glyf
	 */
	protected $table;

	protected $offset;
	protected $size;

	// Data
	public $numberOfContours;
	public $xMin;
	public $yMin;
	public $xMax;
	public $yMax;

	public $raw;

	/**
	 * @param glyf $table
	 * @param                 $offset
	 * @param                 $size
	 *
	 * @return Outline
	 */
	public static function init(glyf $table, $offset, $size, BinaryStream $font)
	{
		$font->seek($offset);

		if ($font->readInt16() > -1) {
			/** @var OutlineSimple $glyph */
			$glyph = new OutlineSimple($table, $offset, $size);
		}
		else {
			/** @var OutlineComposite $glyph */
			$glyph = new OutlineComposite($table, $offset, $size);
		}

		$glyph->parse($font);

		return $glyph;
	}

	/**
	 * @return File
	 */
	public function getFont()
	{
		return $this->table->getFont();
	}

	public function __construct(glyf $table, $offset = null, $size = null)
	{
		$this->table  = $table;
		$this->offset = $offset;
		$this->size   = $size;
	}

	public function parse(BinaryStream $font)
	{
		$font->seek($this->offset);

		if (!$this->size) {
			return;
		}

		// $this->raw = $font->read($this->size);
	}

	public function parseData()
	{
		$font = $this->getFont();
		$font->seek($this->offset);

		$this->numberOfContours = $font->readInt16();
		$this->xMin             = $font->readFWord();
		$this->yMin             = $font->readFWord();
		$this->xMax             = $font->readFWord();
		$this->yMax             = $font->readFWord();
	}

	public function encode()
	{
		$font = $this->getFont();

		return $font->write($this->raw, strlen($this->raw));
	}

	public function getSVGContours()
	{
		// Inherit
	}

	public function getGlyphIDs()
	{
		return [];
	}
}

