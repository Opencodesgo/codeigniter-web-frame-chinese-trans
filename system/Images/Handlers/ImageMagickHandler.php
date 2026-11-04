<?php
/**
 * CodeIgniter，图片，处理程序，图像魔术处理程序
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
use Exception;
use Imagick;

/**
 * Class ImageMagickHandler
 * 图像魔术处理程序类。
 *
 * To make this library as compatible as possible with the broadest
 * number of installations, we do not use the Imagick extension,
 * but simply use the command line version.
 * 为了使该库尽可能兼容最广泛的安装环境，我们并未使用 Imagick 扩展，而是直接使用命令行版本。
 *
 * hmm - the width & height accessors at the end use the imagick extension.
 *
 * FIXME - This needs conversion & unit testing, to use the imagick extension
 */
class ImageMagickHandler extends BaseHandler
{
	/**
	 * Stores image resource in memory.
	 * 在内存中存储映像资源
	 *
	 * @var string|null
	 */
	protected $resource;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param  Images $config
	 * @throws ImageException
	 */
	public function __construct($config = null)
	{
		parent::__construct($config);

		// We should never see this, so can't test it
		// @codeCoverageIgnoreStart
		if (! (extension_loaded('imagick') || class_exists('Imagick')))
		{
			throw ImageException::forMissingExtension('IMAGICK');
		}
		// @codeCoverageIgnoreEnd
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the actual resizing of the image.
	 * 处理图像的实际大小调整
	 *
	 * @param boolean $maintainRatio
	 *
	 * @return ImageMagickHandler
	 * @throws Exception
	 */
	public function _resize(bool $maintainRatio = false)
	{
		$source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
		$destination = $this->getResourcePath();

		$escape = '\\';
		if (stripos(PHP_OS, 'WIN') === 0)
		{
			$escape = '';
		}

		$action = $maintainRatio === true
			? ' -resize ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . ' "' . $source . '" "' . $destination . '"'
			: ' -resize ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . "{$escape}! \"" . $source . '" "' . $destination . '"';

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Crops the image.
	 * 裁剪图像
	 *
	 * @return boolean|\CodeIgniter\Images\Handlers\ImageMagickHandler
	 * @throws Exception
	 */
	public function _crop()
	{
		$source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
		$destination = $this->getResourcePath();

		$extent = ' ';
		if ($this->xAxis >= $this->width || $this->yAxis > $this->height)
		{
			$extent = ' -background transparent -extent ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . ' ';
		}

		$action = ' -crop ' . ($this->width ?? 0) . 'x' . ($this->height ?? 0) . '+' . ($this->xAxis ?? 0) . '+' . ($this->yAxis ?? 0) . $extent . escapeshellarg($source) . ' ' . escapeshellarg($destination);

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the rotation of an image resource.
	 * Doesn't save the image, but replaces the current resource.
	 * 处理图像资源的旋转。不保存图像，而是替换当前资源。
	 *
	 * @param integer $angle
	 *
	 * @return $this
	 * @throws Exception
	 */
	protected function _rotate(int $angle)
	{
		$angle = '-rotate ' . $angle;

		$source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
		$destination = $this->getResourcePath();

		$action = ' ' . $angle . ' ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);

		$this->process($action);

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
	 * @throws Exception
	 */
	public function _flatten(int $red = 255, int $green = 255, int $blue = 255)
	{
		$flatten = "-background 'rgb({$red},{$green},{$blue})' -flatten";

		$source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
		$destination = $this->getResourcePath();

		$action = ' ' . $flatten . ' ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);

		$this->process($action);

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
	 * @throws Exception
	 */
	public function _flip(string $direction)
	{
		$angle = $direction === 'horizontal' ? '-flop' : '-flip';

		$source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
		$destination = $this->getResourcePath();

		$action = ' ' . $angle . ' ' . escapeshellarg($source) . ' ' . escapeshellarg($destination);

		$this->process($action);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Get driver version
	 * 获取驱动程序版本
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		$result = $this->process('-version');

		// The first line has the version in it...
		preg_match('/(ImageMagick\s[\S]+)/', $result[0], $matches);

		return str_replace('ImageMagick ', '', $matches[0]);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles all of the grunt work of resizing, etc.
	 * 处理所有调整大小等繁重的工作
	 *
	 * @param string  $action
	 * @param integer $quality
	 *
	 * @return array  Lines of output from shell command
	 * @throws Exception
	 */
	protected function process(string $action, int $quality = 100): array
	{
		// Do we have a vaild library path?
		if (empty($this->config->libraryPath))
		{
			throw ImageException::forInvalidImageLibraryPath($this->config->libraryPath);
		}

		if ($action !== '-version')
		{
			$this->supportedFormatCheck();
		}

		if (! preg_match('/convert$/i', $this->config->libraryPath))
		{
			$this->config->libraryPath = rtrim($this->config->libraryPath, '/') . '/convert';
		}

		$cmd = $this->config->libraryPath;
		$cmd .= $action === '-version' ? ' ' . $action : ' -quality ' . $quality . ' ' . $action;

		$retval = 1;
		// exec() might be disabled
		if (function_usable('exec'))
		{
			@exec($cmd, $output, $retval);
		}

		// Did it work?
		if ($retval > 0)
		{
			throw ImageException::forImageProcessFailed();
		}

		return $output; // @phpstan-ignore-line
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

		// Copy the file through ImageMagick so that it has
		// a chance to convert file format.
		$action = escapeshellarg($this->resource) . ' ' . escapeshellarg($target);

		$result = $this->process($action, $quality);

		unlink($this->resource);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Image Resource
	 * 获取图像资源。
	 *
	 * This simply creates an image resource handle
	 * based on the type of image being processed.
	 * Since ImageMagick is used on the cli, we need to
	 * ensure we have a temporary file on the server
	 * that we can use.
	 *
	 * To ensure we can use all features, like transparency,
	 * during the process, we'll use a PNG as the temp file type.
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function getResourcePath()
	{
		if (! is_null($this->resource))
		{
			return $this->resource;
		}

		$this->resource = WRITEPATH . 'cache/' . time() . '_' . bin2hex(random_bytes(10)) . '.png';

		$name = basename($this->resource);
		$path = pathinfo($this->resource, PATHINFO_DIRNAME);

		$this->image()->copy($path, $name);

		return $this->resource;
	}

	//--------------------------------------------------------------------

	/**
	 * Make the image resource object if needed
	 * 如果需要，创建图像资源对象。
	 *
	 * @throws Exception
	 */
	protected function ensureResource()
	{
		$this->getResourcePath();

		$this->supportedFormatCheck();
	}

	/**
	 * Check if given image format is supported
	 * 检查是否支持给定的图像格式
	 *
	 * @throws ImageException
	 */
	protected function supportedFormatCheck()
	{
		switch ($this->image()->imageType)
		{
			case IMAGETYPE_WEBP:
				if (! in_array('WEBP', Imagick::queryFormats(), true))
				{
					throw ImageException::forInvalidImageCreate(lang('images.webpNotSupported'));
				}
				break;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Handler-specific method for overlaying text on an image.
	 * 用于在图像上覆盖文本的特定处理程序方法
	 *
	 * @param string $text
	 * @param array  $options
	 *
	 * @throws Exception
	 */
	protected function _text(string $text, array $options = [])
	{
		$cmd = '';

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

		// Font
		if (! empty($options['fontPath']))
		{
			$cmd .= " -font '{$options['fontPath']}'";
		}

		if (isset($options['hAlign']) && isset($options['vAlign']))
		{
			switch ($options['hAlign'])
			{
				case 'left':
					$xAxis   = $options['hOffset'] + $options['padding'];
					$yAxis   = $options['vOffset'] + $options['padding'];
					$gravity = $options['vAlign'] === 'top' ? 'NorthWest' : 'West';
					if ($options['vAlign'] === 'bottom')
					{
						$gravity = 'SouthWest';
						$yAxis   = $options['vOffset'] - $options['padding'];
					}
					break;
				case 'center':
					$xAxis   = $options['hOffset'] + $options['padding'];
					$yAxis   = $options['vOffset'] + $options['padding'];
					$gravity = $options['vAlign'] === 'top' ? 'North' : 'Center';
					if ($options['vAlign'] === 'bottom')
					{
						$yAxis   = $options['vOffset'] - $options['padding'];
						$gravity = 'South';
					}
					break;
				case 'right':
					$xAxis   = $options['hOffset'] - $options['padding'];
					$yAxis   = $options['vOffset'] + $options['padding'];
					$gravity = $options['vAlign'] === 'top' ? 'NorthEast' : 'East';
					if ($options['vAlign'] === 'bottom')
					{
						$gravity = 'SouthEast';
						$yAxis   = $options['vOffset'] - $options['padding'];
					}
					break;
			}

			$xAxis = $xAxis >= 0 ? '+' . $xAxis : $xAxis; // @phpstan-ignore-line
			$yAxis = $yAxis >= 0 ? '+' . $yAxis : $yAxis; // @phpstan-ignore-line

			$cmd .= " -gravity {$gravity} -geometry {$xAxis}{$yAxis}"; // @phpstan-ignore-line
		}

		// Color
		if (isset($options['color']))
		{
			list($r, $g, $b) = sscanf("#{$options['color']}", '#%02x%02x%02x');

			$cmd .= " -fill 'rgba({$r},{$g},{$b},{$options['opacity']})'";
		}

		// Font Size - use points....
		if (isset($options['fontSize']))
		{
			$cmd .= " -pointsize {$options['fontSize']}";
		}

		// Text
		$cmd .= " -annotate 0 '{$text}'";

		$source      = ! empty($this->resource) ? $this->resource : $this->image()->getPathname();
		$destination = $this->getResourcePath();

		$cmd = " '{$source}' {$cmd} '{$destination}'";

		$this->process($cmd);
	}

	//--------------------------------------------------------------------

	/**
	 * Return the width of an image.
	 * 返回图像的宽度
	 *
	 * @return integer
	 */
	public function _getWidth()
	{
		return imagesx(imagecreatefromstring(file_get_contents($this->resource)));
	}

	/**
	 * Return the height of an image.
	 * 返回图像的高度
	 *
	 * @return integer
	 */
	public function _getHeight()
	{
		return imagesy(imagecreatefromstring(file_get_contents($this->resource)));
	}

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser. This is especially an issue
	 * with images taken by smartphones who always store the image up-right,
	 * but set the orientation flag to display it correctly.
	 * 读取图像的EXIF信息，并修改其方向，以便在浏览器中正确显示。
	 * 这尤其适用于智能手机拍摄的图片，因为这些设备通常会将图片存储为上右朝向，但会设置方向标志以确保正确显示。
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
				return $this->rotate(90)
								->flip('horizontal');
			case 6:
				return $this->rotate(90);
			case 7:
				return $this->rotate(270)
								->flip('horizontal');
			case 8:
				return $this->rotate(270);
			default:
				return $this;
		}
	}
}
