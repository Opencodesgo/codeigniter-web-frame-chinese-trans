<?php
/**
 * CodeIgniter，图片，处理程序，基本处理器
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
use CodeIgniter\Images\Image;
use CodeIgniter\Images\ImageHandlerInterface;
use Config\Images;
use InvalidArgumentException;

/**
 * Base image handling implementation
 * 基本图像处理实现。
 */
abstract class BaseHandler implements ImageHandlerInterface
{
	/**
	 * Configuration settings.
	 * 配置设置
	 *
	 * @var Images
	 */
	protected $config;

	/**
	 * The image/file instance
	 * image/file实例
	 *
	 * @var \CodeIgniter\Images\Image
	 */
	protected $image;

	/**
	 * Whether the image file has been confirmed.
	 * 镜像文件是否已确认
	 *
	 * @var boolean
	 */
	protected $verified = false;

	/**
	 * Image width.
	 * 图像宽
	 *
	 * @var integer
	 */
	protected $width = 0;

	/**
	 * Image height.
	 * 图像高
	 *
	 * @var integer
	 */
	protected $height = 0;

	/**
	 * File permission mask.
	 * 文件权限掩码
	 *
	 * @var integer
	 */
	protected $filePermissions = 0644;

	/**
	 * X-axis.
	 * X轴
	 *
	 * @var integer|null
	 */
	protected $xAxis = 0;

	/**
	 * Y-axis.
	 * Y轴
	 *
	 * @var integer|null
	 */
	protected $yAxis = 0;

	/**
	 * Master dimensioning.
	 * 主尺寸
	 *
	 * @var string
	 */
	protected $masterDim = 'auto';

	/**
	 * Default options for text watermarking.
	 * 文本水印的默认选项
	 *
	 * @var array
	 */
	protected $textDefaults = [
		'fontPath'     => null,
		'fontSize'     => 16,
		'color'        => 'ffffff',
		'opacity'      => 1.0,
		'vAlign'       => 'bottom',
		'hAlign'       => 'center',
		'vOffset'      => 0,
		'hOffset'      => 0,
		'padding'      => 0,
		'withShadow'   => false,
		'shadowColor'  => '000000',
		'shadowOffset' => 3,
	];

	/**
	 * Image types with support for transparency.
	 * 支持透明度的图像类型
	 *
	 * @var array
	 */
	protected $supportTransparency = [
		IMAGETYPE_PNG,
		IMAGETYPE_WEBP,
	];

	/**
	 * Temporary image used by the different engines.
	 * 不同引擎使用的临时图像
	 *
	 * @var resource|null
	 */
	protected $resource;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param Images|null $config
	 */
	public function __construct($config = null)
	{
		$this->config = $config ?? new Images();
	}

	//--------------------------------------------------------------------

	/**
	 * Sets another image for this handler to work on.
	 * Keeps us from needing to continually instantiate the handler.
	 * 为该处理器设置另一张图像，使其能够工作。避免我们不断需要实例化处理器。
	 *
	 * @param string $path
	 *
	 * @return $this
	 */
	public function withFile(string $path)
	{
		// Clear out the old resource so that
		// it doesn't try to use a previous image
		$this->resource = null;
		$this->verified = false;

		$this->image = new Image($path, true);

		$this->image->getProperties(false);
		$this->width  = $this->image->origWidth;
		$this->height = $this->image->origHeight;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Make the image resource object if needed
	 * 如果需要，创建图像资源对象。
	 */
	protected abstract function ensureResource();

	//--------------------------------------------------------------------

	/**
	 * Returns the image instance.
	 * 返回图像实例
	 *
	 * @return Image
	 */
	public function getFile()
	{
		return $this->image;
	}

	/**
	 * Verifies that a file has been supplied and it is an image.
	 * 验证是否提供了一个文件并且它是一个映像。
	 *
	 * @return Image  The image instance
	 * @throws ImageException
	 */
	protected function image(): Image
	{
		if ($this->verified)
		{
			return $this->image;
		}

		// Verify withFile has been called
		if (empty($this->image))
		{
			throw ImageException::forMissingImage();
		}

		// Verify the loaded image is an Image instance
		if (! $this->image instanceof Image)
		{
			throw ImageException::forInvalidPath();
		}

		// File::__construct has verified the file exists - make sure it is an image
		if (! is_int($this->image->imageType))
		{
			throw ImageException::forFileNotSupported();
		}

		// Note that the image has been verified
		$this->verified = true;

		return $this->image;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the temporary image used during the image processing.
	 * Good for extending the system or doing things this library
	 * is not intended to do.
	 * 返回图像处理过程中使用的临时图像。适用于扩展系统或执行该库未设计的功能。
	 *
	 * @return resource
	 */
	public function getResource()
	{
		$this->ensureResource();
		return $this->resource;
	}

	//--------------------------------------------------------------------

	/**
	 * Load the temporary image used during the image processing.
	 * Some functions e.g. save() will only copy and not compress
	 * your image otherwise.
	 * 加载图像处理过程中使用的临时图像。
	 * 某些函数（例如 save()）会直接复制图像，而不会进行压缩。
	 *
	 * @return $this
	 */
	public function withResource()
	{
		$this->ensureResource();
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Resize the image
	 * 调整图像大小
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param boolean $maintainRatio If true, will get the closest match possible while keeping aspect ratio true.
	 * @param string  $masterDim
	 *
	 * @return BaseHandler
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		// If the target width/height match the source, then we have nothing to do here.
		if ($this->image()->origWidth === $width && $this->image()->origHeight === $height)
		{
			return $this;
		}

		$this->width  = $width;
		$this->height = $height;

		if ($maintainRatio)
		{
			$this->masterDim = $masterDim;
			$this->reproportion();
		}

		return $this->_resize($maintainRatio);
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image to the desired height and width. If one of the height/width values
	 * is not provided, that value will be set the appropriate value based on offsets and
	 * image dimensions.
	 * 将图像裁剪为指定的高度和宽度。
	 * 如果未提供高度或宽度的任一值，则会根据偏移量和图像尺寸自动设置相应的值。
	 *
	 * @param integer|null $width
	 * @param integer|null $height
	 * @param integer|null $x             X-axis coord to start cropping from the left of image
	 * @param integer|null $y             Y-axis coord to start cropping from the top of image
	 * @param boolean      $maintainRatio
	 * @param string       $masterDim
	 *
	 * @return $this
	 */
	public function crop(int $width = null, int $height = null, int $x = null, int $y = null, bool $maintainRatio = false, string $masterDim = 'auto')
	{
		$this->width  = $width;
		$this->height = $height;
		$this->xAxis  = $x;
		$this->yAxis  = $y;

		if ($maintainRatio)
		{
			$this->masterDim = $masterDim;
			$this->reproportion();
		}

		$result = $this->_crop();

		$this->xAxis = null;
		$this->yAxis = null;

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Changes the stored image type to indicate the new file format to use when saving.
	 * Does not touch the actual resource.
	 * 将存储的图像类型更改为保存时使用的新型文件格式，但不会影响实际资源。
	 *
	 * @param integer $imageType A PHP imageType constant, e.g. https://www.php.net/manual/en/function.image-type-to-mime-type.php
	 *
	 * @return $this
	 */
	public function convert(int $imageType)
	{
		$this->image()->imageType = $imageType;
		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Rotates the image on the current canvas.
	 * 旋转当前画布上的图像
	 *
	 * @param float $angle
	 *
	 * @return $this
	 */
	public function rotate(float $angle)
	{
		// Allowed rotation values
		$degs = [
			90.0,
			180.0,
			270.0,
		];

		if (! in_array($angle, $degs, true))
		{
			throw ImageException::forMissingAngle();
		}

		// cast angle as an int, for our use
		$angle = (int) $angle;

		// Reassign the width and height
		if ($angle === 90 || $angle === 270)
		{
			$temp         = $this->height;
			$this->width  = $this->height;
			$this->height = $temp;
		}

		// Call the Handler-specific version.
		$this->_rotate($angle);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Flattens transparencies, default white background
	 * 平化透明度，默认为白色背景。
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 *
	 * @return $this
	 */
	public function flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$this->width  = $this->image()->origWidth;
		$this->height = $this->image()->origHeight;

		return $this->_flatten($red, $green, $blue);
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to flattening an image's transparencies.
	 * 特定于处理程序的方法，使图像的透明度平坦化。
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 *
	 * @return   $this
	 * @internal
	 */
	protected abstract function _flatten(int $red = 255, int $green = 255, int $blue = 255);

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to handle rotating an image in 90 degree increments.
	 * 处理以90度增量旋转图像的特定处理程序方法
	 *
	 * @param integer $angle
	 *
	 * @return mixed
	 */
	protected abstract function _rotate(int $angle);

	//--------------------------------------------------------------------

	/**
	 * Flips an image either horizontally or vertically.
	 * 水平或垂直翻转图像
	 *
	 * @param string $dir Either 'vertical' or 'horizontal'
	 *
	 * @return $this
	 */
	public function flip(string $dir = 'vertical')
	{
		$dir = strtolower($dir);

		if ($dir !== 'vertical' && $dir !== 'horizontal')
		{
			throw ImageException::forInvalidDirection($dir);
		}

		return $this->_flip($dir);
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method to handle flipping an image along its
	 * horizontal or vertical axis.
	 * 用于处理图像沿水平或垂直轴翻转的特定方法
	 *
	 * @param string $direction
	 *
	 * @return $this
	 */
	protected abstract function _flip(string $direction);

	//--------------------------------------------------------------------

	/**
	 * Overlays a string of text over the image.
	 * 在图像上覆盖一串文本。
	 *
	 * Valid options:
	 *
	 *  - color         Text Color (hex number)
	 *  - shadowColor   Color of the shadow (hex number)
	 *  - hAlign        Horizontal alignment: left, center, right
	 *  - vAlign        Vertical alignment: top, middle, bottom
	 *  - hOffset
	 *  - vOffset
	 *  - fontPath
	 *  - fontSize
	 *  - shadowOffset
	 *
	 * @param string $text
	 * @param array  $options
	 *
	 * @return $this
	 */
	public function text(string $text, array $options = [])
	{
		$options                = array_merge($this->textDefaults, $options);
		$options['color']       = trim($options['color'], '# ');
		$options['shadowColor'] = trim($options['shadowColor'], '# ');

		$this->_text($text, $options);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 * 用于在图像上覆盖文本的特定处理程序方法
	 *
	 * @param string $text
	 * @param array  $options
	 */
	protected abstract function _text(string $text, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Handles the actual resizing of the image.
	 * 处理图像的实际大小调整
	 *
	 * @param boolean $maintainRatio
	 *
	 * @return $this
	 */
	public abstract function _resize(bool $maintainRatio = false);

	//--------------------------------------------------------------------

	/**
	 * Crops the image.
	 * 裁剪图像
	 *
	 * @return $this
	 */
	public abstract function _crop();

	//--------------------------------------------------------------------

	/**
	 * Return image width.
	 * 返回图像宽度
	 *
	 * @return integer
	 */
	public abstract function _getWidth();

	//--------------------------------------------------------------------

	/**
	 * Return the height of an image.
	 * 返回图像的高度
	 *
	 * @return integer
	 */
	public abstract function _getHeight();

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser. This is especially an issue
	 * with images taken by smartphones who always store the image up-right,
	 * but set the orientation flag to display it correctly.
	 * 读取图像的EXIF信息，并修改其方向，以便在浏览器中正确显示。
	 *
	 * @param boolean $silent If true, will ignore exceptions when PHP doesn't support EXIF.
	 *
	 * @return $this
	 */
	public function reorient(bool $silent = false)
	{
		$orientation = $this->getEXIF('Orientation', $silent);

		switch ($orientation)
		{
			case 2:
				return $this->flip('horizontal');
			case 3:
				return $this->rotate(180);
			case 4:
				return $this->rotate(180)
								->flip('horizontal');
			case 5:
				return $this->rotate(270)
								->flip('horizontal');
			case 6:
				return $this->rotate(270);
			case 7:
				return $this->rotate(90)
								->flip('horizontal');
			case 8:
				return $this->rotate(90);
			default:
				return $this;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 * 尽可能从图像中提取EXIF信息。返回信息的数组，如果未找到则返回 null。
	 *
	 * EXIF data is only supported fr JPEG & TIFF formats.
	 *
	 * @param string|null $key    If specified, will only return this piece of EXIF data.
	 * @param boolean     $silent If true, will not throw our own exceptions.
	 *
	 * @throws ImageException
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null, bool $silent = false)
	{
		if (! function_exists('exif_read_data'))
		{
			if ($silent)
			{
				return null;
			}

			throw ImageException::forEXIFUnsupported(); // @codeCoverageIgnore
		}

		$exif = null; // default
		switch ($this->image()->imageType)
		{
			case IMAGETYPE_JPEG:
			case IMAGETYPE_TIFF_II:
				$exif = @exif_read_data($this->image()->getPathname());
				if (! is_null($key) && is_array($exif))
				{
					$exif = $exif[$key] ?? false;
				}
		}

		return $exif;
	}

	//--------------------------------------------------------------------

	/**
	 * Combine cropping and resizing into a single command.
	 * 将裁剪和调整大小合并到一个命令中。
	 *
	 * Supported positions:
	 *  - top-left
	 *  - top
	 *  - top-right
	 *  - left
	 *  - center
	 *  - right
	 *  - bottom-left
	 *  - bottom
	 *  - bottom-right
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param string  $position
	 *
	 * @return BaseHandler
	 */
	public function fit(int $width, int $height = null, string $position = 'center')
	{
		$origWidth  = $this->image()->origWidth;
		$origHeight = $this->image()->origHeight;

		list($cropWidth, $cropHeight) = $this->calcAspectRatio($width, $height, $origWidth, $origHeight);

		if (is_null($height))
		{
			$height = ceil(($width / $cropWidth) * $cropHeight);
		}

		list($x, $y) = $this->calcCropCoords($cropWidth, $cropHeight, $origWidth, $origHeight, $position);

		return $this->crop($cropWidth, $cropHeight, $x, $y)
						->resize($width, $height);
	}

	//--------------------------------------------------------------------

	/**
	 * Calculate image aspect ratio.
	 * 计算图像长宽比
	 *
	 * @param integer|float      $width
	 * @param integer|float|null $height
	 * @param integer|float      $origWidth
	 * @param integer|float      $origHeight
	 *
	 * @return array
	 */
	protected function calcAspectRatio($width, $height = null, $origWidth = 0, $origHeight = 0): array
	{
		if (empty($origWidth) || empty($origHeight))
		{
			throw new InvalidArgumentException('You must supply the parameters: origWidth, origHeight.');
		}

		// If $height is null, then we have it easy.
		// Calc based on full image size and be done.
		if (is_null($height))
		{
			$height = ($width / $origWidth) * $origHeight;

			return [
				$width,
				(int) $height,
			];
		}

		$xRatio = $width / $origWidth;
		$yRatio = $height / $origHeight;

		if ($xRatio > $yRatio)
		{
			return [
				$origWidth,
				(int) ($origWidth * $height / $width),
			];
		}

		return [
			(int) ($origHeight * $width / $height),
			$origHeight,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Based on the position, will determine the correct x/y coords to
	 * crop the desired portion from the image.
	 * 根据位置，将确定正确的 x/y 坐标，以从图像中裁剪出所需部分。
	 *
	 * @param integer|float $width
	 * @param integer|float $height
	 * @param integer|float $origWidth
	 * @param integer|float $origHeight
	 * @param string        $position
	 *
	 * @return array
	 */
	protected function calcCropCoords($width, $height, $origWidth, $origHeight, $position): array
	{
		$position = strtolower($position);

		$x = $y = 0;

		switch ($position)
		{
			case 'top-left':
				$x = 0;
				$y = 0;
				break;
			case 'top':
				$x = floor(($origWidth - $width) / 2);
				$y = 0;
				break;
			case 'top-right':
				$x = $origWidth - $width;
				$y = 0;
				break;
			case 'left':
				$x = 0;
				$y = floor(($origHeight - $height) / 2);
				break;
			case 'center':
				$x = floor(($origWidth - $width) / 2);
				$y = floor(($origHeight - $height) / 2);
				break;
			case 'right':
				$x = ($origWidth - $width);
				$y = floor(($origHeight - $height) / 2);
				break;
			case 'bottom-left':
				$x = 0;
				$y = $origHeight - $height;
				break;
			case 'bottom':
				$x = floor(($origWidth - $width) / 2);
				$y = $origHeight - $height;
				break;
			case 'bottom-right':
				$x = ($origWidth - $width);
				$y = $origHeight - $height;
				break;
		}

		return [
			$x,
			$y,
		];
	}

	//--------------------------------------------------------------------

	/**
	 * Get the version of the image library in use.
	 * 获取正在使用的映像库的版本
	 *
	 * @return string
	 */
	public abstract function getVersion();

	//--------------------------------------------------------------------

	/**
	 * Saves any changes that have been made to file.
	 * 将所做的任何更改保存到文件中。
	 *
	 * Example:
	 *    $image->resize(100, 200, true)
	 *          ->save($target);
	 *
	 * @param string|null $target
	 * @param integer     $quality
	 *
	 * @return boolean
	 */
	public abstract function save(string $target = null, int $quality = 90);

	//--------------------------------------------------------------------

	/**
	 * Does the driver-specific processing of the image.
	 * 对图像进行特定于驱动程序的处理
	 *
	 * @param string $action
	 *
	 * @return mixed
	 */
	protected abstract function process(string $action);

	//--------------------------------------------------------------------

	/**
	 * Provide access to the Image class' methods if they don't exist
	 * on the handler itself.
	 * 如果处理器本身没有该方法，则提供访问图像类方法的途径。
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public function __call(string $name, array $args = [])
	{
		if (method_exists($this->image(), $name))
		{
			return $this->image()->$name(...$args);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Re-proportion Image Width/Height
	 * 重新比例图像的宽度/高度。
	 *
	 * When creating thumbs, the desired width/height
	 * can end up warping the image due to an incorrect
	 * ratio between the full-sized image and the thumb.
	 * 在创建缩略图时，由于全尺寸图像与缩略图之间的比例不正确，可能导致图像变形。
	 *
	 * This function lets us re-proportion the width/height
	 * if users choose to maintain the aspect ratio when resizing.
	 *
	 * @return void
	 */
	protected function reproportion()
	{
		if (($this->width === 0 && $this->height === 0) || $this->image()->origWidth === 0 || $this->image()->origHeight === 0 || (! ctype_digit((string) $this->width) && ! ctype_digit((string) $this->height)) || ! ctype_digit((string) $this->image()->origWidth) || ! ctype_digit((string) $this->image()->origHeight)
		)
		{
			return;
		}

		// Sanitize
		$this->width  = (int) $this->width;
		$this->height = (int) $this->height;

		if ($this->masterDim !== 'width' && $this->masterDim !== 'height')
		{
			if ($this->width > 0 && $this->height > 0)
			{
				$this->masterDim = ((($this->image()->origHeight / $this->image()->origWidth) - ($this->height / $this->width)) < 0) ? 'width' : 'height';
			}
			else
			{
				$this->masterDim = ($this->height === 0) ? 'width' : 'height';
			}
		}
		elseif (($this->masterDim === 'width' && $this->width === 0) || ($this->masterDim === 'height' && $this->height === 0)
		)
		{
			return;
		}

		if ($this->masterDim === 'width')
		{
			$this->height = (int) ceil($this->width * $this->image()->origHeight / $this->image()->origWidth);
		}
		else
		{
			$this->width = (int) ceil($this->image()->origWidth * $this->height / $this->image()->origHeight);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Return image width.
	 * 返回图像宽度。
	 *
	 * accessor for testing; not part of interface
	 * 测试访问器；不是界面的一部分。
	 *
	 * @return integer
	 */
	public function getWidth()
	{
		return ($this->resource !== null) ? $this->_getWidth() : $this->width;
	}

	/**
	 * Return image height.
	 * 返回图像高度。
	 *
	 * accessor for testing; not part of interface
	 *
	 * @return integer
	 */
	public function getHeight()
	{
		return ($this->resource !== null) ? $this->_getHeight() : $this->height;
	}
}
