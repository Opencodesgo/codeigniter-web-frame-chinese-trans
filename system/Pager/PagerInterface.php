<?php
/**
 * CodeIgniter，分页，Pager 接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Pager;

/**
 * Expected behavior for a Pager
 * 分页的预期行为。
 */
interface PagerInterface
{
	/**
	 * Handles creating and displaying the
	 * 控件的创建和显示
	 *
	 * @param string $group
	 * @param string $template The output template alias to render.
	 *
	 * @return string
	 */
	public function links(string $group = 'default', string $template = 'default'): string;

	//--------------------------------------------------------------------

	/**
	 * Creates simple Next/Previous links, instead of full pagination.
	 * 创建简单的Next/Previous链接，而不是完整的分页。
	 *
	 * @param string $group
	 * @param string $template
	 *
	 * @return string
	 */
	public function simpleLinks(string $group = 'default', string $template = 'default'): string;

	//--------------------------------------------------------------------

	/**
	 * Allows for a simple, manual, form of pagination where all of the data
	 * is provided by the user. The URL is the current URI.
	 * 支持一种简单的手动分页方式，所有数据均由用户提供。URL 为当前的 URI。
	 *
	 * @param integer $page
	 * @param integer $perPage
	 * @param integer $total
	 * @param string  $template The output template alias to render.
	 *
	 * @return string
	 */
	public function makeLinks(int $page, int $perPage, int $total, string $template = 'default'): string;

	//--------------------------------------------------------------------

	/**
	 * Stores a set of pagination data for later display. Most commonly used
	 * by the model to automate the process.
	 * 存储一组分页数据，用于后续显示。模型通常使用此功能来自动化该过程。
	 *
	 * @param string  $group
	 * @param integer $page
	 * @param integer $perPage
	 * @param integer $total
	 *
	 * @return mixed
	 */
	public function store(string $group, int $page, int $perPage, int $total);

	//--------------------------------------------------------------------

	/**
	 * Sets the path that an aliased group of links will use.
	 * 设置别名链接组将使用的路径
	 *
	 * @param string $path
	 * @param string $group
	 *
	 * @return mixed
	 */
	public function setPath(string $path, string $group = 'default');

	//--------------------------------------------------------------------

	/**
	 * Returns the total number of pages.
	 * 返回总页数
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getPageCount(string $group = 'default'): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the number of the current page of results.
	 * 返回当前结果页的编号
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getCurrentPage(string $group = 'default'): int;

	//--------------------------------------------------------------------

	/**
	 * Returns the URI for a specific page for the specified group.
	 * 返回指定组的特定页面的URI
	 *
	 * @param integer|null $page
	 * @param string       $group
	 * @param boolean      $returnObject
	 *
	 * @return string|\CodeIgniter\HTTP\URI
	 */
	public function getPageURI(int $page = null, string $group = 'default', bool $returnObject = false);

	//--------------------------------------------------------------------

	/**
	 * Tells whether this group of results has any more pages of results.
	 * 告诉这组结果是否有更多的结果页
	 *
	 * @param string $group
	 *
	 * @return boolean
	 */
	public function hasMore(string $group = 'default'): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the first page.
	 * 返回第一页
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getFirstPage(string $group = 'default');

	//--------------------------------------------------------------------

	/**
	 * Returns the last page, if we have a total that we can calculate with.
	 * 如果我们有一个可以计算的总数，则返回最后一页。
	 *
	 * @param string $group
	 *
	 * @return integer|null
	 */
	public function getLastPage(string $group = 'default');

	//--------------------------------------------------------------------

	/**
	 * Returns the full URI to the next page of results, or null.
	 * 将完整的URI返回到下一页的结果，或者返回null。
	 *
	 * @param string $group
	 *
	 * @return string|null
	 */
	public function getNextPageURI(string $group = 'default');

	//--------------------------------------------------------------------

	/**
	 * Returns the full URL to the previous page of results, or null.
	 * 返回前一页结果的完整URL，或者为空。
	 *
	 * @param string $group
	 *
	 * @return string|null
	 */
	public function getPreviousPageURI(string $group = 'default');

	//--------------------------------------------------------------------

	/**
	 * Returns the number of results per page that should be shown.
	 * 返回每页应显示的结果数
	 *
	 * @param string $group
	 *
	 * @return integer
	 */
	public function getPerPage(string $group = 'default'): int;

	//--------------------------------------------------------------------

	/**
	 * Returns an array with details about the results, including
	 * total, per_page, current_page, last_page, next_url, prev_url, from, to.
	 * Does not include the actual data. This data is suitable for adding
	 * a 'data' object to with the result set and converting to JSON.
	 * 返回一个包含结果详细信息的数组，包括总数、每页数量、当前页码、最后一页、下一页URL、上一页URL、起始位置和结束位置。
	 * 不包含实际数据。这些数据可用于向结果集添加“data”对象，并转换为JSON格式。
	 *
	 * @param string $group
	 *
	 * @return array
	 */
	public function getDetails(string $group = 'default'): array;

	//--------------------------------------------------------------------
}
