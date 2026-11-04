<?php
/**
 * CodeIgniter，HTTP，文件，文件收集
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Files;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class FileCollection
 * 文件收集类。
 *
 * Provides easy access to uploaded files for a request.
 * 为请求提供对上传文件的方便访问。
 */
class FileCollection
{
	/**
	 * An array of UploadedFile instances for any files
	 * uploaded as part of this request.
	 * Populated the first time either files(), file(), or hasFile()
	 * is called.
	 * 此请求中上传的文件所对应的 UploadedFile 实例数组。
	 *
	 * @var array|null
	 */
	protected $files;

	//--------------------------------------------------------------------

	/**
	 * Returns an array of all uploaded files that were found.
	 * Each element in the array will be an instance of UploadedFile.
	 * The key of each element will be the client filename.
	 * 返回所有找到的上传文件的数组。
	 * 数组中的每个元素都是 UploadedFile 类的实例，其键为客户端文件名。
	 *
	 * @return array|null
	 */
	public function all()
	{
		$this->populateFiles();

		return $this->files;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to get a single file from the collection of uploaded files.
	 * 尝试从上传的文件集合中获取单个文件
	 *
	 * @param string $name
	 *
	 * @return UploadedFile|null
	 */
	public function getFile(string $name)
	{
		$this->populateFiles();

		if ($this->hasFile($name))
		{
			if (strpos($name, '.') !== false)
			{
				$name         = explode('.', $name);
				$uploadedFile = $this->getValueDotNotationSyntax($name, $this->files);
				return ($uploadedFile instanceof UploadedFile) ?
					 $uploadedFile : null;
			}

			if (array_key_exists($name, $this->files))
			{
				$uploadedFile = $this->files[$name];
				return  ($uploadedFile instanceof UploadedFile) ?
					$uploadedFile : null;
			}
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Verify if a file exist in the collection of uploaded files and is have been uploaded with multiple option.
	 * 验证文件是否存在于已上传文件的集合中，并且已使用多个选项上传。
	 *
	 * @param string $name
	 *
	 * @return array|null
	 */
	public function getFileMultiple(string $name)
	{
		$this->populateFiles();

		if ($this->hasFile($name))
		{
			if (strpos($name, '.') !== false)
			{
				$name         = explode('.', $name);
				$uploadedFile = $this->getValueDotNotationSyntax($name, $this->files);

				return (is_array($uploadedFile) && ($uploadedFile[0] instanceof UploadedFile)) ?
					$uploadedFile : null;
			}

			if (array_key_exists($name, $this->files))
			{
				$uploadedFile = $this->files[$name];
				return (is_array($uploadedFile) && ($uploadedFile[0] instanceof UploadedFile)) ?
					$uploadedFile : null;
			}
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether an uploaded file with name $fileID exists in
	 * this request.
	 * 检查上传的文件 $fileID 是否存在于当前请求中
	 *
	 * @param string $fileID The name of the uploaded file (from the input)
	 *
	 * @return boolean
	 */
	public function hasFile(string $fileID): bool
	{
		$this->populateFiles();

		if (strpos($fileID, '.') !== false)
		{
			$segments = explode('.', $fileID);

			$el = $this->files;

			foreach ($segments as $segment)
			{
				if (! array_key_exists($segment, $el))
				{
					return false;
				}

				$el = $el[$segment];
			}

			return true;
		}

		return isset($this->files[$fileID]);
	}

	//--------------------------------------------------------------------

	/**
	 * Taking information from the $_FILES array, it creates an instance
	 * of UploadedFile for each one, saving the results to this->files.
	 * 从 $_FILES 数组中获取信息后，为每个条目创建一个 UploadedFile 实例，并将结果保存到 this->files 中。
	 *
	 * Called by files(), file(), and hasFile()
	 */
	protected function populateFiles()
	{
		if (is_array($this->files))
		{
			return;
		}

		$this->files = [];

		if (empty($_FILES))
		{
			return;
		}

		$files = $this->fixFilesArray($_FILES);

		foreach ($files as $name => $file)
		{
			$this->files[$name] = $this->createFileObject($file);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Given a file array, will create UploadedFile instances. Will
	 * loop over an array and create objects for each.
	 * 给定一个文件数组，将创建 UploadedFile 实例。遍历数组并为每个元素创建对象。
	 *
	 * @param array $array
	 *
	 * @return array|UploadedFile
	 */
	protected function createFileObject(array $array)
	{
		if (! isset($array['name']))
		{
			$output = [];

			foreach ($array as $key => $values)
			{
				if (! is_array($values))
				{
					continue;
				}

				$output[$key] = $this->createFileObject($values);
			}

			return $output;
		}

		return new UploadedFile(
				$array['tmp_name'] ?? null, $array['name'] ?? null, $array['type'] ?? null, $array['size'] ?? null, $array['error'] ?? null
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Reformats the odd $_FILES array into something much more like
	 * we would expect, with each object having its own array.
	 * 将杂乱的 $_FILES 数组重新格式化，使其更符合我们的预期，每个对象都有自己的数组。
	 *
	 * Thanks to Jack Sleight on the PHP Manual page for the basis
	 * of this method.
	 * 感谢 Jack Sleight 在 PHP 手册页面上提供了此方法的基础。
	 *
	 * @see http://php.net/manual/en/reserved.variables.files.php#118294
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fixFilesArray(array $data): array
	{
		$output = [];

		foreach ($data as $name => $array)
		{
			foreach ($array as $field => $value)
			{
				$pointer = &$output[$name];

				if (! is_array($value))
				{
					$pointer[$field] = $value;
					continue;
				}

				$stack    = [&$pointer];
				$iterator = new RecursiveIteratorIterator(
						new RecursiveArrayIterator($value), RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($iterator as $key => $val)
				{
					array_splice($stack, $iterator->getDepth() + 1);
					$pointer = &$stack[count($stack) - 1];
					$pointer = &$pointer[$key];
					$stack[] = &$pointer;
					if (! $iterator->hasChildren())
					{
						$pointer[$field] = $val;
					}
				}
			}
		}

		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Navigate through a array looking for a particular index
	 * 在数组中导航以查找特定索引
	 *
	 * @param array $index The index sequence we are navigating down
	 * @param array $value The portion of the array to process
	 *
	 * @return mixed
	 */
	protected function getValueDotNotationSyntax(array $index, array $value)
	{
		if (! empty($index))
		{
			$currentIndex = array_shift($index);
		}
		if (isset($currentIndex) && is_array($index) && $index && is_array($value[$currentIndex]) && $value[$currentIndex])
		{
			return $this->getValueDotNotationSyntax($index, $value[$currentIndex]);
		}

		return (isset($currentIndex) && isset($value[$currentIndex])) ? $value[$currentIndex] : null;
	}
}
