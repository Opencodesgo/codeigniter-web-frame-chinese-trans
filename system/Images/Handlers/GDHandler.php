<?php
/**
 * CodeIgniter，图片，处理程序，GD 处理器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Images\Handlers;

use CodeIgniter\Images\Exceptions\ImageException;
use Config\Images;

/**
 * Image handler for GD package
 * GD包的图像处理程序。
 */
class GDHandler extends BaseHandler
{
	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param  Images|null $config
	 * @throws ImageException
	 */
	public function __construct($config = null)
	{
		parent::__construct($config);

		// We should never see this, so can't test it
		// @codeCoverageIgnoreStart
		if (! extension_loaded('gd'))
		{
			throw ImageException::forMissingExtension('GD');
		}
		// @codeCoverageIgnoreEnd
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the rotation of an image resource.
	 * Doesn't save the image, but replaces the current resource.
	 * 处理图像资源的旋转。不保存图像，而是替换当前资源。
	 *
	 * @param integer $angle
	 *
	 * @return boolean
	 */
	protected function _rotate(int $angle): bool
	{
		// Create the image handle
		$srcImg = $this->createImage();

		// Set the background color
		// This won't work with transparent PNG files so we are
		// going to have to figure out how to determine the color
		// of the alpha channel in a future release.

		$white = imagecolorallocate($srcImg, 255, 255, 255);

		// Rotate it!
		$destImg = imagerotate($srcImg, $angle, $white);

		// Kill the file handles
		imagedestroy($srcImg);

		$this->resource = $destImg;

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Flattens transparencies
	 * 使幻灯片平坦
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 *
	 * @return $this
	 */
	public function _flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$srcImg = $this->createImage();

		if (function_exists('imagecreatetruecolor'))
		{
			$create = 'imagecreatetruecolor';
			$copy   = 'imagecopyresampled';
		}
		else
		{
			$create = 'imagecreate';
			$copy   = 'imagecopyresized';
		}
		$dest = $create($this->width, $this->height);

		$matte = imagecolorallocate($dest, $red, $green, $blue);

		imagefilledrectangle($dest, 0, 0, $this->width, $this->height, $matte);
		imagecopy($dest, $srcImg, 0, 0, 0, 0, $this->width, $this->height);

		// Kill the file handles
		imagedestroy($srcImg);

		$this->resource = $dest;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Flips an image along it's vertical or horizontal axis.
	 * 沿垂直或水平轴翻转图像
	 *
	 * @param string $direction
	 *
	 * @return $this
	 */
	public function _flip(string $direction)
	{
		$srcImg = $this->createImage();

		$angle = $direction === 'horizontal' ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL;

		imageflip($srcImg, $angle);

		$this->resource = $srcImg;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get GD version
	 * 获取GD版本
	 *
	 * @return mixed
	 */
	public function getVersion()
	{
		if (function_exists('gd_info'))
		{
			$gdVersion = @gd_info();

			return preg_replace('/\D/', '', $gdVersion['GD Version']);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Resizes the image.
	 * 调整图像的大小
	 *
	 * @return GDHandler
	 */
	public function _resize(bool $maintainRatio = false)
	{
		return $this->process('resize');
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image.
	 * 修剪图像
	 *
	 * @return GDHandler
	 */
	public function _crop()
	{
		return $this->process('crop');
	}

	//--------------------------------------------------------------------

	/**
	 * Handles all of the grunt work of resizing, etc.
	 * 处理所有调整大小等繁重的工作
	 *
	 * @param string $action
	 *
	 * @return $this
	 */
	protected function process(string $action)
	{
		$origWidth  = $this->image()->origWidth;
		$origHeight = $this->image()->origHeight;

		if ($action === 'crop')
		{
			// Reassign the source width/height if cropping
			$origWidth  = $this->width;
			$origHeight = $this->height;

			// Modify the "original" width/height to the new
			// values so that methods that come after have the
			// correct size to work with.
			$this->image()->origHeight = $this->height;
			$this->image()->origWidth  = $this->width;
		}

		// Create the image handle
		$src = $this->createImage();

		if (function_exists('imagecreatetruecolor'))
		{
			$create = 'imagecreatetruecolor';
			$copy   = 'imagecopyresampled';
		}
		else
		{
			$create = 'imagecreate';
			$copy   = 'imagecopyresized';
		}

		$dest = $create($this->width, $this->height);

		// for png and webp we can actually preserve transparency
		if (in_array($this->image()->imageType, $this->supportTransparency, true))
		{
			imagealphablending($dest, false);
			imagesavealpha($dest, true);
		}

		$copy($dest, $src, 0, 0, $this->xAxis, $this->yAxis, $this->width, $this->height, $origWidth, $origHeight);

		imagedestroy($src);
		$this->resource = $dest;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves any changes that have been made to file. If no new filename is
	 * provided, the existing image is overwritten, otherwise a copy of the
	 * file is made at $target.
	 * 保存对文件所做的任何更改。如果未提供新的文件名，则覆盖现有图像；否则在 $target 处创建文件的副本。
	 *
	 * Example:
	 *    $image->resize(100, 200, true)
	 *          ->save();
	 *
	 * @param string|null $target
	 * @param integer     $quality
	 *
	 * @return boolean
	 */
	public function save(string $target = null, int $quality = 90): bool
	{
		$original = $target;
		$target   = empty($target) ? $this->image()->getPathname() : $target;

		// If no new resource has been created, then we're
		// simply copy the existing one.
		if (empty($this->resource) && $quality === 100)
		{
			if ($original === null)
			{
				return true;
			}

			$name = basename($target);
			$path = pathinfo($target, PATHINFO_DIRNAME);

			return $this->image()->copy($path, $name);
		}

		$this->ensureResource();

		switch ($this->image()->imageType)
		{
			case IMAGETYPE_GIF:
				if (! function_exists('imagegif'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.gifNotSupported'));
				}

				if (! @imagegif($this->resource, $target))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			case IMAGETYPE_JPEG:
				if (! function_exists('imagejpeg'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.jpgNotSupported'));
				}

				if (! @imagejpeg($this->resource, $target, $quality))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			case IMAGETYPE_PNG:
				if (! function_exists('imagepng'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.pngNotSupported'));
				}

				if (! @imagepng($this->resource, $target))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			case IMAGETYPE_WEBP:
				if (! function_exists('imagewebp'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.webpNotSupported'));
				}

				if (! @imagewebp($this->resource, $target))
				{
					throw ImageException::forSaveFailed();
				}
				break;
			default:
				throw ImageException::forInvalidImageCreate();
		}

		imagedestroy($this->resource);

		chmod($target, $this->filePermissions);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Create Image Resource
	 * 创建镜像资源。
	 *
	 * This simply creates an image resource handle
	 * based on the type of image being processed
	 * 这会根据正在处理的图像类型创建一个图像资源句柄。
	 *
	 * @param string $path
	 * @param string $imageType
	 *
	 * @return resource|boolean
	 */
	protected function createImage(string $path = '', string $imageType = '')
	{
		if ($this->resource !== null)
		{
			return $this->resource;
		}

		if ($path === '')
		{
			$path = $this->image()->getPathname();
		}

		if ($imageType === '')
		{
			$imageType = $this->image()->imageType;
		}

		return $this->getImageResource($path, $imageType);
	}

	//--------------------------------------------------------------------

	/**
	 * Make the image resource object if needed
	 * 如果需要，创建图像资源对象。
	 */
	protected function ensureResource()
	{
		if ($this->resource === null)
		{
			// if valid image type, make corresponding image resource
			$this->resource = $this->getImageResource(
				$this->image()->getPathname(), $this->image()->imageType
			);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Check if image type is supported and return image resource
	 * 检查是否支持映像类型并返回映像资源
	 *
	 * @param string  $path      Image path
	 * @param integer $imageType Image type
	 *
	 * @return resource|boolean
	 * @throws ImageException
	 */
	protected function getImageResource(string $path, int $imageType)
	{
		switch ($imageType)
		{
			case IMAGETYPE_GIF:
				if (! function_exists('imagecreatefromgif'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.gifNotSupported'));
				}

				return imagecreatefromgif($path);
			case IMAGETYPE_JPEG:
				if (! function_exists('imagecreatefromjpeg'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.jpgNotSupported'));
				}

				return imagecreatefromjpeg($path);
			case IMAGETYPE_PNG:
				if (! function_exists('imagecreatefrompng'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.pngNotSupported'));
				}

				return imagecreatefrompng($path);
			case IMAGETYPE_WEBP:
				if (! function_exists('imagecreatefromwebp'))
				{
					throw ImageException::forInvalidImageCreate(lang('Images.webpNotSupported'));
				}

				return imagecreatefromwebp($path);
			default:
				throw ImageException::forInvalidImageCreate('Ima');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Add text overlay to an image.
	 * 为图像添加文本叠加
	 *
	 * @param string $text
	 * @param array  $options
	 *
	 * @return void
	 */
	protected function _text(string $text, array $options = [])
	{
		// Reverse the vertical offset
		// When the image is positioned at the bottom
		// we don't want the vertical offset to push it
		// further down. We want the reverse, so we'll
		// invert the offset. Note: The horizontal
		// offset flips itself automatically

		if ($options['vAlign'] === 'bottom')
		{
			$options['vOffset'] = $options['vOffset'] * -1;
		}

		if ($options['hAlign'] === 'right')
		{
			$options['hOffset'] = $options['hOffset'] * -1;
		}

		// Set font width and height
		// These are calculated differently depending on
		// whether we are using the true type font or not
		if (! empty($options['fontPath']))
		{
			if (function_exists('imagettfbbox'))
			{
				$temp = imagettfbbox($options['fontSize'], 0, $options['fontPath'], $text);
				$temp = $temp[2] - $temp[0];

				$fontwidth = $temp / strlen($text);
			}
			else
			{
				$fontwidth = $options['fontSize'] - ($options['fontSize'] / 4);
			}

			$fontheight = $options['fontSize'];
		}
		else
		{
			$fontwidth  = imagefontwidth($options['fontSize']);
			$fontheight = imagefontheight($options['fontSize']);
		}

		$options['fontheight'] = $fontheight;
		$options['fontwidth']  = $fontwidth;

		// Set base X and Y axis values
		$xAxis = $options['hOffset'] + $options['padding'];
		$yAxis = $options['vOffset'] + $options['padding'];

		// Set vertical alignment
		if ($options['vAlign'] === 'middle')
		{
			// Don't apply padding when you're in the middle of the image.
			$yAxis += ($this->image()->origHeight / 2) + ($fontheight / 2) - $options['padding'] - $fontheight - $options['shadowOffset'];
		}
		elseif ($options['vAlign'] === 'bottom')
		{
			$yAxis = ($this->image()->origHeight - $fontheight - $options['shadowOffset'] - ($fontheight / 2)) - $yAxis;
		}

		// Set horizontal alignment
		if ($options['hAlign'] === 'right')
		{
			$xAxis += ($this->image()->origWidth - ($fontwidth * strlen($text)) - $options['shadowOffset']) - (2 * $options['padding']);
		}
		elseif ($options['hAlign'] === 'center')
		{
			$xAxis += floor(($this->image()->origWidth - ($fontwidth * strlen($text))) / 2);
		}

		$options['xAxis'] = $xAxis;
		$options['yAxis'] = $yAxis;

		if ($options['withShadow'])
		{
			// Offset from text
			$options['xShadow'] = $xAxis + $options['shadowOffset'];
			$options['yShadow'] = $yAxis + $options['shadowOffset'];

			$this->textOverlay($text, $options, true);
		}

		$this->textOverlay($text, $options);
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 * 用于在图像上覆盖文本的特定处理程序方法
	 *
	 * @param string  $text
	 * @param array   $options
	 * @param boolean $isShadow Whether we are drawing the dropshadow or actual text
	 *
	 * @return void
	 */
	protected function textOverlay(string $text, array $options = [], bool $isShadow = false)
	{
		$src = $this->createImage();

		/* Set RGB values for shadow
		 *
		 * Get the rest of the string and split it into 2-length
		 * hex values:
		 */
		$opacity = ($options['opacity'] * 127);

		// Allow opacity to be applied to the text
		imagealphablending($src, true);

		$color = $isShadow ? $options['shadowColor'] : $options['color'];

		// shorthand hex, #f00
		if (strlen($color) === 3)
		{
			$color = implode('', array_map('str_repeat', str_split($color), [2, 2, 2]));
		}

		$color = str_split(substr($color, 0, 6), 2);
		$color = imagecolorclosestalpha($src, hexdec($color[0]), hexdec($color[1]), hexdec($color[2]), $opacity);

		$xAxis = $isShadow ? $options['xShadow'] : $options['xAxis'];
		$yAxis = $isShadow ? $options['yShadow'] : $options['yAxis'];

		// Add the shadow to the source image
		if (! empty($options['fontPath']))
		{
			// We have to add fontheight because imagettftext locates the bottom left corner, not top-left corner.
			imagettftext($src, $options['fontSize'], 0, $xAxis, $yAxis + $options['fontheight'], $color, $options['fontPath'], $text);
		}
		else
		{
			imagestring($src, $options['fontSize'], $xAxis, $yAxis, $text, $color);
		}

		$this->resource = $src;
	}

	//--------------------------------------------------------------------

	/**
	 * Return image width.
	 * 返回图像宽度
	 *
	 * @return integer
	 */
	public function _getWidth()
	{
		return imagesx($this->resource);
	}

	/**
	 * Return image height.
	 * 返回图像高度
	 *
	 * @return integer
	 */
	public function _getHeight()
	{
		return imagesy($this->resource);
	}
}
