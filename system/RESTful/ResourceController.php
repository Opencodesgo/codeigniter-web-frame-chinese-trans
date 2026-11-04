<?php
/**
 * CodeIgniter，RESTful，资源控制器
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

use CodeIgniter\API\ResponseTrait;

/**
 * An extendable controller to provide a RESTful API for a resource.
 * 为资源提供RESTful API的可扩展控制器。
 */
class ResourceController extends BaseResource
{
	use ResponseTrait;

	/**
	 * Return an array of resource objects, themselves in array format
	 * 返回资源对象的数组，其本身为数组格式。
	 *
	 * @return mixed
	 */
	public function index()
	{
		return $this->fail(lang('RESTful.notImplemented', ['index']), 501);
	}

	/**
	 * Return the properties of a resource object
	 * 返回资源对象的属性
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function show($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['show']), 501);
	}

	/**
	 * Return a new resource object, with default properties
	 * 返回一个具有默认属性的新资源对象
	 *
	 * @return mixed
	 */
	public function new()
	{
		return $this->fail(lang('RESTful.notImplemented', ['new']), 501);
	}

	/**
	 * Create a new resource object, from "posted" parameters
	 * 根据“张贴”参数创建一个新的资源对象
	 *
	 * @return mixed
	 */
	public function create()
	{
		return $this->fail(lang('RESTful.notImplemented', ['create']), 501);
	}

	/**
	 * Return the editable properties of a resource object
	 * 返回资源对象的可编辑属性
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function edit($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['edit']), 501);
	}

	/**
	 * Add or update a model resource, from "posted" properties
	 * 从“posted”属性中添加或更新模型资源
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function update($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['update']), 501);
	}

	/**
	 * Delete the designated resource object from the model
	 * 从模型中删除指定的资源对象
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 */
	public function delete($id = null)
	{
		return $this->fail(lang('RESTful.notImplemented', ['delete']), 501);
	}

	/**
	 * Set/change the expected response representation for returned objects
	 * 设置/更改返回对象的预期响应表示
	 *
	 * @param string $format
	 *
	 * @return void
	 */
	public function setFormat(string $format = 'json')
	{
		if (in_array($format, ['json', 'xml'], true))
		{
			$this->format = $format;
		}
	}
}
