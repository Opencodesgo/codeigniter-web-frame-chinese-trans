<?php
/**
 * 配置，格式化
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Format\FormatterInterface;

class Format extends BaseConfig
{
	/**
	 * --------------------------------------------------------------------------
	 * Available Response Formats	可用的响应格式
	 * --------------------------------------------------------------------------
	 *
	 * When you perform content negotiation with the request, these are the
	 * available formats that your application supports. This is currently
	 * only used with the API\ResponseTrait. A valid Formatter must exist
	 * for the specified format.
	 * 当您对请求进行内容协商时，这些是您的应用程序支持的可用格式。
	 * 这只使用了API \ ResponseTrait。必须以指定的格式存在一个有效的格式化程序。
	 *
	 * These formats are only checked when the data passed to the respond()
	 * method is an array.
	 *
	 * @var string[]
	 */
	public $supportedResponseFormats = [
		'application/json',
		'application/xml', // machine-readable XML
		'text/xml', // human-readable XML
	];

	/**
	 * --------------------------------------------------------------------------
	 * Formatters	格式化
	 * --------------------------------------------------------------------------
	 *
	 * Lists the class to use to format responses with of a particular type.
	 * For each mime type, list the class that should be used. Formatters
	 * can be retrieved through the getFormatter() method.
	 * 列出要用于格式化特定类型的响应的类。
	 * 对于每种 mime 类型，列出应使用的类。可以通过 getFormatter() 方法获取格式化器。
	 *
	 * @var array<string, string>
	 */
	public $formatters = [
		'application/json' => 'CodeIgniter\Format\JSONFormatter',
		'application/xml'  => 'CodeIgniter\Format\XMLFormatter',
		'text/xml'         => 'CodeIgniter\Format\XMLFormatter',
	];

	/**
	 * --------------------------------------------------------------------------
	 * Formatters Options	格式化器选项
	 * --------------------------------------------------------------------------
	 *
	 * Additional Options to adjust default formatters behaviour.
	 * For each mime type, list the additional options that should be used.
	 * 调整默认格式化程序行为的附加选项。
	 * 对于每种mime类型，列出应该使用的其他选项。
	 *
	 * @var array<string, int>
	 */
	public $formatterOptions = [
		'application/json' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
		'application/xml'  => 0,
		'text/xml'         => 0,
	];

	//--------------------------------------------------------------------

	/**
	 * A Factory method to return the appropriate formatter for the given mime type.
	 * 一个Factory方法，用于返回给定mime类型的适当格式化程序。
	 *
	 * @param string $mime
	 *
	 * @return FormatterInterface
	 *
	 * @deprecated This is an alias of `\CodeIgniter\Format\Format::getFormatter`. Use that instead.
	 */
	public function getFormatter(string $mime)
	{
		return Services::format()->getFormatter($mime);
	}
}
