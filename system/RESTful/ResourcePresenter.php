<?php
/**
 * CodeIgniter，RESTful，资源的 Presenter
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\RESTful;

/**
 * An extendable controller to help provide a UI for a resource.
 * 帮助为资源提供UI的可扩展控制器。
 */
class ResourcePresenter extends BaseResource
{
	/**
	 * Present a view of resource objects
	 * 呈现资源对象的视图
	 *
	 * @return mixed
	 */
	public function index()
	{
		return lang('RESTful.notImplemented', ['index']);
	}

	/**
	 * Present a view to present a specific resource object
	 * 呈现一个视图来呈现一个特定的资源对象
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function show($id = null)
	{
		return lang('RESTful.notImplemented', ['show']);
	}

	/**
	 * Present a view to present a new single resource object
	 * 呈现一个视图来呈现一个新的单一资源对象
	 *
	 * @return mixed
	 */
	public function new()
	{
		return lang('RESTful.notImplemented', ['new']);
	}

	/**
	 * Process the creation/insertion of a new resource object.
	 * This should be a POST.
	 * 处理新资源对象的创建/插入操作。应使用 POST 方法。
	 *
	 * @return mixed
	 */
	public function create()
	{
		return lang('RESTful.notImplemented', ['create']);
	}

	/**
	 * Present a view to edit the properties of a specific resource object
	 * 提供一个视图来编辑特定资源对象的属性
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function edit($id = null)
	{
		return lang('RESTful.notImplemented', ['edit']);
	}

	/**
	 * Process the updating, full or partial, of a specific resource object.
	 * This should be a POST.
	 * 处理特定资源对象的完整或部分更新。应使用 POST 方法。
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function update($id = null)
	{
		return lang('RESTful.notImplemented', ['update']);
	}

	/**
	 * Present a view to confirm the deletion of a specific resource object
	 * 提供一个视图以确认删除特定的资源对象
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function remove($id = null)
	{
		return lang('RESTful.notImplemented', ['remove']);
	}

	/**
	 * Process the deletion of a specific resource object
	 * 处理删除指定资源对象
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function delete($id = null)
	{
		return lang('RESTful.notImplemented', ['delete']);
	}
}
