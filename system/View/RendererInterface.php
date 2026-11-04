<?php
/**
 * CodeIgniter，视图，渲染器接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\View;

/**
 * Interface RendererInterface
 * 渲染器接口。
 *
 * The interface used for displaying Views and/or theme files.
 * 用于显示视图和/或主题文件的接口。
 */
interface RendererInterface
{
	/**
	 * Builds the output based upon a file name and any
	 * data that has already been set.
	 * 根据文件名以及已设置的任何数据来生成输出
	 *
	 * @param string  $view
	 * @param array   $options  Reserved for 3rd-party uses since
	 *                          it might be needed to pass additional info
	 *                          to other template engines.
	 * @param boolean $saveData Whether to save data for subsequent calls
	 *
	 * @return string
	 */
	public function render(string $view, array $options = null, bool $saveData = false): string;

	//--------------------------------------------------------------------

	/**
	 * Builds the output based upon a string and any
	 * data that has already been set.
	 * 根据字符串以及已设置的任何数据来构建输出
	 *
	 * @param string  $view     The view contents
	 * @param array   $options  Reserved for 3rd-party uses since
	 *                          it might be needed to pass additional info
	 *                          to other template engines.
	 * @param boolean $saveData Whether to save data for subsequent calls
	 *
	 * @return string
	 */
	public function renderString(string $view, array $options = null, bool $saveData = false): string;

	//--------------------------------------------------------------------

	/**
	 * Sets several pieces of view data at once.
	 * 一次设置多个视图数据
	 *
	 * @param array  $data
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If 'raw', no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setData(array $data = [], string $context = null);

	//--------------------------------------------------------------------

	/**
	 * Sets a single piece of view data.
	 * 设置单个视图数据
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param string $context The context to escape it for: html, css, js, url
	 *                        If 'raw' no escaping will happen
	 *
	 * @return RendererInterface
	 */
	public function setVar(string $name, $value = null, string $context = null);

	//--------------------------------------------------------------------

	/**
	 * Removes all of the view data from the system.
	 * 从系统中删除所有视图数据
	 *
	 * @return RendererInterface
	 */
	public function resetData();

	//--------------------------------------------------------------------
}
