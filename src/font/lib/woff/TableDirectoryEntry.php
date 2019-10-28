<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace isszz\captcha\font\lib\woff;

use isszz\captcha\font\lib\table\DirectoryEntry;

/**
 * WOFF font file table directory entry.
 *
 * @package php-font-lib
 */
class TableDirectoryEntry extends DirectoryEntry
{
	public $origLength;

	public function __construct(File $font)
	{
		parent::__construct($font);
	}

	public function parse()
	{
		parent::parse();

		$font             = $this->font;
		$this->offset     = $font->readUInt32();
		$this->length     = $font->readUInt32();
		$this->origLength = $font->readUInt32();
		$this->checksum   = $font->readUInt32();
	}
}
