<?php
/**
 * CodeIgniter，图片，图像处理程序接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Images;

/**
 * Expected behavior of an Image handler
 * 图像处理程序的预期行为。
 */
interface ImageHandlerInterface
{
	/**
	 * Resize the image
	 * 调整图像大小
	 *
	 * @param integer $width
	 * @param integer $height
	 * @param boolean $maintainRatio If true, will get the closest match possible while keeping aspect ratio true.
	 * @param string  $masterDim
	 *
	 * @return $this
	 */
	public function resize(int $width, int $height, bool $maintainRatio = false, string $masterDim = 'auto');

	//--------------------------------------------------------------------

	/**
	 * Crops the image to the desired height and width. If one of the height/width values
	 * is not provided, that value will be set the appropriate value based on offsets and
	 * image dimensions.
	 * 将图像裁剪为指定的高度和宽度。
	 * 如果未提供高度或宽度的任一值，则会根据偏移量和图像尺寸自动设置相应的数值。
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
	public function crop(int $width = null, int $height = null, int $x = null, int $y = null, bool $maintainRatio = false, string $masterDim = 'auto');

	//--------------------------------------------------------------------

	/**
	 * Changes the stored image type to indicate the new file format to use when saving.
	 * Does not touch the actual resource.
	 * 将存储的图像类型更改为保存时使用的新型文件格式，但不会影响实际资源。
	 *
	 * @param integer $imageType A PHP imagetype constant, e.g. https://www.php.net/manual/en/function.image-type-to-mime-type.php
	 *
	 * @return $this
	 */
	public function convert(int $imageType);

	//--------------------------------------------------------------------

	/**
	 * Rotates the image on the current canvas.
	 * 旋转当前画布上的图像
	 *
	 * @param float $angle
	 *
	 * @return $this
	 */
	public function rotate(float $angle);

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
	public function flatten(int $red = 255, int $green = 255, int $blue = 255);

	//--------------------------------------------------------------------

	/**
	 * Reads the EXIF information from the image and modifies the orientation
	 * so that displays correctly in the browser.
	 * 读取图像的EXIF信息，并修改其方向，以便在浏览器中正确显示。
	 *
	 * @return ImageHandlerInterface
	 */
	public function reorient();

	//--------------------------------------------------------------------

	/**
	 * Retrieve the EXIF information from the image, if possible. Returns
	 * an array of the information, or null if nothing can be found.
	 * 尽可能从图像中提取EXIF信息。返回信息的数组，如果未找到则返回 null。
	 *
	 * @param string|null $key If specified, will only return this piece of EXIF data.
	 *
	 * @return mixed
	 */
	public function getEXIF(string $key = null);

	//--------------------------------------------------------------------

	/**
	 * Flip an image horizontally or vertically
	 * 水平或垂直翻转图像
	 *
	 * @param string $dir Direction to flip, either 'vertical' or 'horizontal'
	 *
	 * @return $this
	 */
	public function flip(string $dir = 'vertical');

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
	 * @return $this
	 */
	public function fit(int $width, int $height, string $position);

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
	public function text(string $text, array $options = []);

	//--------------------------------------------------------------------

	/**
	 * Saves any changes that have been made to file.
	 * 将所做的任何更改保存到文件中。
	 *
	 * Example:
	 *    $image->resize(100, 200, true)
	 *          ->save($target);
	 *
	 * @param string  $target
	 * @param integer $quality
	 *
	 * @return boolean
	 */
	public function save(string $target = null, int $quality = 90);
}
