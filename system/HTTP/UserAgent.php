<?php
/**
 * CodeIgniter，HTTP，用户代理
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use Config\UserAgents;

/**
 * Abstraction for an HTTP user agent
 * HTTP用户代理的抽象。
 */
class UserAgent
{
	/**
	 * Current user-agent
	 * 当前用户代理
	 *
	 * @var string
	 */
	protected $agent = '';

	/**
	 * Flag for if the user-agent belongs to a browser
	 * 用户代理是否属于浏览器的标志
	 *
	 * @var boolean
	 */
	protected $isBrowser = false;

	/**
	 * Flag for if the user-agent is a robot
	 * 如果用户代理是机器人，则标志。
	 *
	 * @var boolean
	 */
	protected $isRobot = false;

	/**
	 * Flag for if the user-agent is a mobile browser
	 * 如果用户代理是移动浏览器，则标志。
	 *
	 * @var boolean
	 */
	protected $isMobile = false;

	/**
	 * Holds the config file contents.
	 * 保存配置文件的内容
	 *
	 * @var UserAgents
	 */
	protected $config;

	/**
	 * Current user-agent platform
	 * 当前用户代理平台
	 *
	 * @var string
	 */
	protected $platform = '';

	/**
	 * Current user-agent browser
	 * 当前用户代理浏览器
	 *
	 * @var string
	 */
	protected $browser = '';

	/**
	 * Current user-agent version
	 * 当前用户代理版本
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Current user-agent mobile name
	 * 当前用户代理手机名
	 *
	 * @var string
	 */
	protected $mobile = '';

	/**
	 * Current user-agent robot name
	 * 当前用户代理机器人名称
	 *
	 * @var string
	 */
	protected $robot = '';

	/**
	 * HTTP Referer
	 * HTTP引用页
	 *
	 * @var mixed
	 */
	protected $referrer;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * 构造函数。
	 *
	 * Sets the User Agent and runs the compilation routine
	 * 设置用户代理并运行编译例程。
	 *
	 * @param null|UserAgents $config
	 */
	public function __construct(UserAgents $config = null)
	{
		$this->config = $config ?? new UserAgents();

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
			$this->compileData();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Is Browser
	 * 是否浏览器
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isBrowser(string $key = null): bool
	{
		if (! $this->isBrowser)
		{
			return false;
		}

		// No need to be specific, it's a browser
		// 不需要具体说明，这是一个浏览器。
		if ($key === null)
		{
			return true;
		}

		// Check for a specific browser
		// 检查特定的浏览器
		return (isset($this->config->browsers[$key]) && $this->browser === $this->config->browsers[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is Robot
	 * 机器人
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isRobot(string $key = null): bool
	{
		if (! $this->isRobot)
		{
			return false;
		}

		// No need to be specific, it's a robot
		// 不需要具体说明，这是个机器人。
		if ($key === null)
		{
			return true;
		}

		// Check for a specific robot
		return (isset($this->config->robots[$key]) && $this->robot === $this->config->robots[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is Mobile
	 * 是否手机号
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isMobile(string $key = null): bool
	{
		if (! $this->isMobile)
		{
			return false;
		}

		// No need to be specific, it's a mobile
		// 不用多说，这是手机。
		if ($key === null)
		{
			return true;
		}

		// Check for a specific robot
		// 检查一个特定的机器人
		return (isset($this->config->mobiles[$key]) && $this->mobile === $this->config->mobiles[$key]);
	}

	//--------------------------------------------------------------------

	/**
	 * Is this a referral from another site?
	 * 这是从其他网站转来的吗？
	 *
	 * @return boolean
	 */
	public function isReferral(): bool
	{
		if (! isset($this->referrer))
		{
			if (empty($_SERVER['HTTP_REFERER']))
			{
				$this->referrer = false;
			}
			else
			{
				$refererHost = @parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
				$ownHost     = parse_url(\base_url(), PHP_URL_HOST);

				$this->referrer = ($refererHost && $refererHost !== $ownHost);
			}
		}

		return $this->referrer;
	}

	//--------------------------------------------------------------------

	/**
	 * Agent String
	 * 代理字符串
	 *
	 * @return string
	 */
	public function getAgentString(): string
	{
		return $this->agent;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Platform
	 * 获取平台
	 *
	 * @return string
	 */
	public function getPlatform(): string
	{
		return $this->platform;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Browser Name
	 * 获取浏览器名称
	 *
	 * @return string
	 */
	public function getBrowser(): string
	{
		return $this->browser;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 * 获取浏览器版本
	 *
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	//--------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 * 获取机器人名称
	 *
	 * @return string
	 */
	public function getRobot(): string
	{
		return $this->robot;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 * 获取移动设备
	 *
	 * @return string
	 */
	public function getMobile(): string
	{
		return $this->mobile;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the referrer
	 * 找到推荐人
	 *
	 * @return string
	 */
	public function getReferrer(): string
	{
		return empty($_SERVER['HTTP_REFERER']) ? '' : trim($_SERVER['HTTP_REFERER']);
	}

	//--------------------------------------------------------------------

	/**
	 * Parse a custom user-agent string
	 * 解析自定义用户代理字符串
	 *
	 * @param string $string
	 *
	 * @return void
	 */
	public function parse(string $string)
	{
		// Reset values
		$this->isBrowser = false;
		$this->isRobot   = false;
		$this->isMobile  = false;
		$this->browser   = '';
		$this->version   = '';
		$this->mobile    = '';
		$this->robot     = '';

		// Set the new user-agent string and parse it, unless empty
		// 设置新的user-agent字符串并解析它，除非为空。
		$this->agent = $string;

		if (! empty($string))
		{
			$this->compileData();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 * 编译用户代理数据
	 *
	 * @return void
	 */
	protected function compileData()
	{
		$this->setPlatform();

		foreach (['setRobot', 'setBrowser', 'setMobile'] as $function)
		{
			if ($this->$function() === true)
			{
				break;
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Platform
	 * 设置平台
	 *
	 * @return boolean
	 */
	protected function setPlatform(): bool
	{
		if (is_array($this->config->platforms) && $this->config->platforms)
		{
			foreach ($this->config->platforms as $key => $val)
			{
				if (preg_match('|' . preg_quote($key) . '|i', $this->agent))
				{
					$this->platform = $val;

					return true;
				}
			}
		}

		$this->platform = 'Unknown Platform';

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Browser
	 * 设置浏览器
	 *
	 * @return boolean
	 */
	protected function setBrowser(): bool
	{
		if (is_array($this->config->browsers) && $this->config->browsers)
		{
			foreach ($this->config->browsers as $key => $val)
			{
				if (preg_match('|' . $key . '.*?([0-9\.]+)|i', $this->agent, $match))
				{
					$this->isBrowser = true;
					$this->version   = $match[1];
					$this->browser   = $val;
					$this->setMobile();

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Robot
	 * 设置机器人
	 *
	 * @return boolean
	 */
	protected function setRobot(): bool
	{
		if (is_array($this->config->robots) && $this->config->robots)
		{
			foreach ($this->config->robots as $key => $val)
			{
				if (preg_match('|' . preg_quote($key) . '|i', $this->agent))
				{
					$this->isRobot = true;
					$this->robot   = $val;
					$this->setMobile();

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 * 设置移动设备
	 *
	 * @return boolean
	 */
	protected function setMobile(): bool
	{
		if (is_array($this->config->mobiles) && $this->config->mobiles)
		{
			foreach ($this->config->mobiles as $key => $val)
			{
				if (false !== (stripos($this->agent, $key)))
				{
					$this->isMobile = true;
					$this->mobile   = $val;

					return true;
				}
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs the original Agent String when cast as a string.
	 * 当转换为字符串时，输出原始代理字符串。
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->getAgentString();
	}
}
