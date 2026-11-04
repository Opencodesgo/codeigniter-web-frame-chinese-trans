<?php
/**
 * CodeIgniter，分页，Pager 渲染器
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
 * Class PagerRenderer
 * Pager 渲染器类。
 *
 * This class is passed to the view that describes the pagination,
 * and is used to get the link information and provide utility
 * methods needed to work with pagination.
 * 该类被传递给描述分页的视图，用于获取链接信息，并提供处理分页所需的实用方法。
 */
class PagerRenderer
{
	/**
	 * First page number.
	 * 起始页码
	 *
	 * @var integer
	 */
	protected $first;

	/**
	 * Last page number.
	 * 最后页码
	 *
	 * @var integer
	 */
	protected $last;

	/**
	 * Current page number.
	 * 当前页码
	 *
	 * @var integer
	 */
	protected $current;

	/**
	 * Total number of items.
	 * 项目总数
	 *
	 * @var integer
	 */
	protected $total;

	/**
	 * Total number of pages.
	 * 页数
	 *
	 * @var integer
	 */
	protected $pageCount;

	/**
	 * URI base for pagination links
	 * 分页链接的URI基本
	 *
	 * @var \CodeIgniter\HTTP\URI
	 */
	protected $uri;

	/**
	 * Segment number used for pagination.
	 * 用于分页的段号
	 *
	 * @var integer
	 */
	protected $segment;

	/**
	 * Name of $_GET parameter
	 *
	 * @var string
	 */
	protected $pageSelector;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param array $details
	 */
	public function __construct(array $details)
	{
		$this->first        = 1;
		$this->last         = $details['pageCount'];
		$this->current      = $details['currentPage'];
		$this->total        = $details['total'];
		$this->uri          = $details['uri'];
		$this->pageCount    = $details['pageCount'];
		$this->segment      = $details['segment'] ?? 0;
		$this->pageSelector = $details['pageSelector'] ?? 'page';
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the total number of links that should appear on either
	 * side of the current page. Adjusts the first and last counts
	 * to reflect it.
	 * 设置当前页面两侧应显示的链接总数。并调整首尾计数以反映该数值。
	 *
	 * @param integer|null $count
	 *
	 * @return PagerRenderer
	 */
	public function setSurroundCount(int $count = null)
	{
		$this->updatePages($count);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if there is a "previous" page before our "first" page.
	 *
	 * @return boolean
	 */
	public function hasPrevious(): bool
	{
		return $this->first > 1;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a URL to the "previous" page. The previous page is NOT the
	 * page before the current page, but is the page just before the
	 * "first" page.
	 *
	 * You MUST call hasPrevious() first, or this value may be invalid.
	 *
	 * @return string|null
	 */
	public function getPrevious()
	{
		if (! $this->hasPrevious())
		{
			return null;
		}

		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, $this->first - 1);
		}
		else
		{
			$uri->setSegment($this->segment, $this->first - 1);
		}

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if there is a "next" page after our "last" page.
	 * 检查在我们的“next”页之后是否有“last”页
	 *
	 * @return boolean
	 */
	public function hasNext(): bool
	{
		return $this->pageCount > $this->last;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a URL to the "next" page. The next page is NOT, the
	 * page after the current page, but is the page that follows the
	 * "last" page.
	 *
	 * You MUST call hasNext() first, or this value may be invalid.
	 *
	 * @return string|null
	 */
	public function getNext()
	{
		if (! $this->hasNext())
		{
			return null;
		}

		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, $this->last + 1);
		}
		else
		{
			$uri->setSegment($this->segment, $this->last + 1);
		}

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the URI of the first page.
	 * 返回第一页的URI
	 *
	 * @return string
	 */
	public function getFirst(): string
	{
		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, 1);
		}
		else
		{
			$uri->setSegment($this->segment, 1);
		}

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the URI of the last page.
	 * 返回最后一页的URI
	 *
	 * @return string
	 */
	public function getLast(): string
	{
		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, $this->pageCount);
		}
		else
		{
			$uri->setSegment($this->segment, $this->pageCount);
		}

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the URI of the current page.
	 * 返回当前页面的URI
	 *
	 * @return string
	 */
	public function getCurrent(): string
	{
		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, $this->current);
		}
		else
		{
			$uri->setSegment($this->segment, $this->current);
		}

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of links that should be displayed. Each link
	 * is represented by another array containing of the URI the link
	 * should go to, the title (number) of the link, and a boolean
	 * value representing whether this link is active or not.
	 * 返回一个应显示的链接数组。每个链接由另一个数组表示，
	 * 该数组包含链接应指向的URI、链接标题（数字）以及一个布尔值，用于表示该链接是否处于激活状态。
	 *
	 * @return array
	 */
	public function links(): array
	{
		$links = [];

		$uri = clone $this->uri;

		for ($i = $this->first; $i <= $this->last; $i ++)
		{
			$links[] = [
				'uri'    => (string) ($this->segment === 0 ? $uri->addQuery($this->pageSelector, $i) : $uri->setSegment($this->segment, $i)),
				'title'  => (int) $i,
				'active' => ($i === $this->current),
			];
		}

		return $links;
	}

	//--------------------------------------------------------------------

	/**
	 * Updates the first and last pages based on $surroundCount,
	 * which is the number of links surrounding the active page
	 * to show.
	 * 根据 $surroundCount（即显示的当前页面周围链接的数量）更新第一页和最后一页。
	 *
	 * @param integer|null $count The new "surroundCount"
	 */
	protected function updatePages(int $count = null)
	{
		if (is_null($count))
		{
			return;
		}

		$this->first = $this->current - $count > 0 ? (int) ($this->current - $count) : 1;
		$this->last  = $this->current + $count <= $this->pageCount ? (int) ($this->current + $count) : (int) $this->pageCount;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if there is a "previous" page before our "first" page.
	 *
	 * @return boolean
	 */
	public function hasPreviousPage(): bool
	{
		return $this->current > 1;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a URL to the "previous" page.
	 * 返回“上一页”的URL。
	 *
	 * You MUST call hasPreviousPage() first, or this value may be invalid.
	 *
	 * @return string|null
	 */
	public function getPreviousPage()
	{
		if (! $this->hasPreviousPage())
		{
			return null;
		}

		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, $this->current - 1);
		}
		else
		{
			$uri->setSegment($this->segment, $this->current - 1);
		}

		return (string) $uri;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if there is a "next" page after our "last" page.
	 *
	 * @return boolean
	 */
	public function hasNextPage(): bool
	{
		return $this->current < $this->last;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a URL to the "next" page.
	 * 返回一个URL到“下一个”页面。
	 *
	 * You MUST call hasNextPage() first, or this value may be invalid.
	 *
	 * @return string|null
	 */
	public function getNextPage()
	{
		if (! $this->hasNextPage())
		{
			return null;
		}

		$uri = clone $this->uri;

		if ($this->segment === 0)
		{
			$uri->addQuery($this->pageSelector, $this->current + 1);
		}
		else
		{
			$uri->setSegment($this->segment, $this->current + 1);
		}

		return (string) $uri;
	}

	/**
	 * Returns the page number of the first page.
	 * 返回第一页的页码
	 *
	 * @return integer
	 */
	public function getFirstPageNumber(): int
	{
		return $this->first;
	}

	/**
	 * Returns the page number of the current page.
	 * 返回当前页的页码
	 *
	 * @return integer
	 */
	public function getCurrentPageNumber(): int
	{
		return $this->current;
	}

	/**
	 * Returns the page number of the last page.
	 * 返回最后一页的页码
	 *
	 * @return integer
	 */
	public function getLastPageNumber(): int
	{
		return $this->last;
	}

	/**
	 * Returns the previous page number.
	 * 返回前一个页码
	 *
	 * @return integer|null
	 */
	public function getPreviousPageNumber(): ?int
	{
		return ($this->current === 1) ? null : $this->current - 1;
	}

	/**
	 * Returns the next page number.
	 * 返回下一个页码
	 *
	 * @return integer|null
	 */
	public function getNextPageNumber(): ?int
	{
		return ($this->current === $this->pageCount) ? null : $this->current + 1;
	}
}
