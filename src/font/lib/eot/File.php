<?php
/**
 * @package php-font-lib
 * @link    https://github.com/PhenX/php-font-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace isszz\captcha\font\lib\eot;

/**
 * EOT font file.
 *
 * @package php-font-lib
 */
class File extends \isszz\captcha\font\lib\truetype\File
{
	const TTEMBED_SUBSET                   = 0x00000001;
	const TTEMBED_TTCOMPRESSED             = 0x00000004;
	const TTEMBED_FAILIFVARIATIONSIMULATED = 0x00000010;
	const TTMBED_EMBEDEUDC                 = 0x00000020;
	const TTEMBED_VALIDATIONTESTS          = 0x00000040; // Deprecated
	const TTEMBED_WEBOBJECT      = 0x00000080;
	const TTEMBED_XORENCRYPTDATA = 0x10000000;

	/**
	 * @var Header
	 */
	public $header;

	public function parseHeader()
	{
		if (!empty($this->header)) {
			return;
		}

		$this->header = new Header($this);
		$this->header->parse();
	}

	public function parse()
	{
		$this->parseHeader();

		$flags = $this->header->data["Flags"];

		if ($flags & self::TTEMBED_TTCOMPRESSED) {
			$mtx_version    = $this->readUInt8();
			$mtx_copy_limit = $this->readUInt8() << 16 | $this->readUInt8() << 8 | $this->readUInt8();
			$mtx_offset_1   = $this->readUInt8() << 16 | $this->readUInt8() << 8 | $this->readUInt8();
			$mtx_offset_2   = $this->readUInt8() << 16 | $this->readUInt8() << 8 | $this->readUInt8();
			/*
			var_dump("$mtx_version $mtx_copy_limit $mtx_offset_1 $mtx_offset_2");

			$pos = $this->pos();
			$size = $mtx_offset_1 - $pos;
			var_dump("pos: $pos");
			var_dump("size: $size");*/
		}

		if ($flags & self::TTEMBED_XORENCRYPTDATA) {
			// Process XOR
		}
		// TODO Read font data ...
	}

	/**
	 * Little endian version of the read method
	 *
	 * @param int $n The number of bytes to read
	 *
	 * @return string
	 */
	public function read($n)
	{
		if ($n < 1) {
			return "";
		}

		$string = fread($this->f, $n);
		$chunks = str_split($string, 2);
		$chunks = array_map("strrev", $chunks);

		return implode("", $chunks);
	}

	public function readUInt32()
	{
		$uint32 = parent::readUInt32();

		return $uint32 >> 16 & 0x0000FFFF | $uint32 << 16 & 0xFFFF0000;
	}

	/**
	 * Get font copyright
	 *
	 * @return string|null
	 */
	public function getFontCopyright()
	{
		return null;
	}

	/**
	 * Get font name
	 *
	 * @return string|null
	 */
	public function getFontName()
	{
		return $this->header->data["FamilyName"];
	}

	/**
	 * Get font subfamily
	 *
	 * @return string|null
	 */
	public function getFontSubfamily()
	{
		return $this->header->data["StyleName"];
	}

	/**
	 * Get font subfamily ID
	 *
	 * @return string|null
	 */
	public function getFontSubfamilyID()
	{
		return $this->header->data["StyleName"];
	}

	/**
	 * Get font full name
	 *
	 * @return string|null
	 */
	public function getFontFullName()
	{
		return $this->header->data["FullName"];
	}

	/**
	 * Get font version
	 *
	 * @return string|null
	 */
	public function getFontVersion()
	{
		return $this->header->data["VersionName"];
	}

	/**
	 * Get font weight
	 *
	 * @return string|null
	 */
	public function getFontWeight()
	{
		return $this->header->data["Weight"];
	}

	/**
	 * Get font Postscript name
	 *
	 * @return string|null
	 */
	public function getFontPostscriptName()
	{
		return null;
	}
}
