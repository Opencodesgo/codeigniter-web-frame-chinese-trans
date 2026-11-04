<?php
/**
 * CodeIgniter，文件，File
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Files;

use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use Config\Mimes;
use SplFileInfo;

/**
 * Wrapper for PHP's built-in SplFileInfo, with goodies.
 * PHP内置SplFileInfo的包装器。
 */
class File extends SplFileInfo
{
	/**
	 * The files size in bytes
	 * 文件大小以字节为单位
	 *
	 * @var integer
	 */
	protected $size;

	/**
	 * Original MimeType
	 * 原始MimeType
	 *
	 * @var null|string
	 */
	protected $originalMimeType = null;

	//--------------------------------------------------------------------

	/**
	 * Run our SplFileInfo constructor with an optional verification
	 * that the path is really a file.
	 * 使用可选的验证来运行 SplFileInfo 构造函数，确保路径确实是一个文件。
	 *
	 * @param string  $path
	 * @param boolean $checkFile
	 */
	public function __construct(string $path, bool $checkFile = false)
	{
		if ($checkFile && ! is_file($path))
		{
			throw FileNotFoundException::forFileNotFound($path);
		}

		parent::__construct($path);
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the file size.
	 * 检索文件大小。
	 *
	 * Implementations SHOULD return the value stored in the "size" key of
	 * the file in the $_FILES array if available, as PHP calculates this based
	 * on the actual size transmitted.
	 * 实现应返回 $_FILES 数组中文件“size”键所存储的值（如果存在），因为 PHP 会根据实际传输的大小来计算该值。
	 *
	 * @return integer The file size in bytes
	 */
	public function getSize()
	{
		return $this->size ?? ($this->size = parent::getSize());
	}

	/**
	 * Retrieve the file size by unit.
	 * 按单位检索文件大小
	 *
	 * @param string $unit
	 *
	 * @return integer|string
	 */
	public function getSizeByUnit(string $unit = 'b')
	{
		switch (strtolower($unit))
		{
			case 'kb':
				return number_format($this->getSize() / 1024, 3);
			case 'mb':
				return number_format(($this->getSize() / 1024) / 1024, 3);
			default:
				return $this->getSize();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the file extension based on the trusted
	 * getType() method. If the mime type is unknown, will return null.
	 * 尝试根据可信的 getType() 方法确定文件扩展名。如果 MIME 类型未知，则返回 null。
	 *
	 * @return string|null
	 */
	public function guessExtension(): ?string
	{
		return Mimes::guessExtensionFromType($this->getMimeType());
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the media type of the file. SHOULD not use information from
	 * the $_FILES array, but should use other methods to more accurately
	 * determine the type of file, like finfo, or mime_content_type().
	 * 检索文件的媒体类型。
	 *
	 * @return string The media type we determined it to be.
	 */
	public function getMimeType(): string
	{
		if (! function_exists('finfo_open'))
		{
			// @codeCoverageIgnoreStart
			return $this->originalMimeType ?? 'application/octet-stream';
			// @codeCoverageIgnoreEnd
		}

		$finfo    = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, $this->getRealPath() ?: $this->__toString());
		finfo_close($finfo);
		return $mimeType;
	}

	//--------------------------------------------------------------------

	/**
	 * Generates a random names based on a simple hash and the time, with
	 * the correct file extension attached.
	 * 根据简单的哈希值和时间生成随机名称，并附上正确的文件扩展名。
	 *
	 * @return string
	 */
	public function getRandomName(): string
	{
		$extension = $this->getExtension();
		$extension = empty($extension) ? '' : '.' . $extension;
		return time() . '_' . bin2hex(random_bytes(10)) . $extension;
	}

	//--------------------------------------------------------------------

	/**
	 * Moves a file to a new location.
	 * 将文件移动到新位置
	 *
	 * @param string      $targetPath
	 * @param string|null $name
	 * @param boolean     $overwrite
	 *
	 * @return File
	 */
	public function move(string $targetPath, string $name = null, bool $overwrite = false)
	{
		$targetPath  = rtrim($targetPath, '/') . '/';
		$name        = $name ?? $this->getBaseName();
		$destination = $overwrite ? $targetPath . $name : $this->getDestination($targetPath . $name);

		$oldName = $this->getRealPath() ?: $this->__toString();

		if (! @rename($oldName, $destination))
		{
			$error = error_get_last();
			throw FileException::forUnableToMove($this->getBasename(), $targetPath, strip_tags($error['message']));
		}

		@chmod($destination, 0777 & ~umask());

		return new File($destination);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the destination path for the move operation where overwriting is not expected.
	 * 返回移动操作的目标路径，其中不期望覆盖。
	 *
	 * First, it checks whether the delimiter is present in the filename, if it is, then it checks whether the
	 * last element is an integer as there may be cases that the delimiter may be present in the filename.
	 * For the all other cases, it appends an integer starting from zero before the file's extension.
	 * 首先,它检查在文件名中是否存在分隔符,如果它是,那么它将检查最后一个元素是否是一个整数,因为可能有,分隔符可能出现在文件名中。
	 * 对于所有其他情况,它将在文件的扩展之前从0开始一个整数。
	 *
	 * @param string  $destination
	 * @param string  $delimiter
	 * @param integer $i
	 *
	 * @return string
	 */
	public function getDestination(string $destination, string $delimiter = '_', int $i = 0): string
	{
		while (is_file($destination))
		{
			$info      = pathinfo($destination);
			$extension = isset($info['extension']) ? '.' . $info['extension'] : '';
			if (strpos($info['filename'], $delimiter) !== false)
			{
				$parts = explode($delimiter, $info['filename']);
				if (is_numeric(end($parts)))
				{
					$i = end($parts);
					array_pop($parts);
					array_push($parts, ++ $i);
					$destination = $info['dirname'] . '/' . implode($delimiter, $parts) . $extension;
				}
				else
				{
					$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++ $i . $extension;
				}
			}
			else
			{
				$destination = $info['dirname'] . '/' . $info['filename'] . $delimiter . ++ $i . $extension;
			}
		}
		return $destination;
	}

	//--------------------------------------------------------------------
}
