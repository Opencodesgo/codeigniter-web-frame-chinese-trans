<?php
/**
 * CodeIgniter，缓存，异常，文件处理器
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Cache\Handlers;

use CodeIgniter\Cache\Exceptions\CacheException;
use Config\Cache;

/**
 * File system cache handler
 * 文件系统缓存处理程序。
 */
class FileHandler extends BaseHandler
{
	/**
	 * Prefixed to all cache names.
	 * 所有缓存名称的前缀
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Where to store cached files on the disk.
	 * 在磁盘上存储缓存文件的位置
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Mode for the stored files.
	 * Must be chmod-safe (octal).
	 * 存储文件的模式。必须是 chmod 安全的（八进制）。
	 *
	 * @var integer
	 *
	 * @see https://www.php.net/manual/en/function.chmod.php
	 */
	protected $mode;

	//--------------------------------------------------------------------

	/**
	 * Constructor.
	 * 构造函数
	 *
	 * @param  Cache $config
	 * @throws CacheException
	 */
	public function __construct(Cache $config)
	{
		if (! property_exists($config, 'file'))
		{
			$config->file = [
				'storePath' => $config->storePath ?? WRITEPATH . 'cache',
				'mode'      => 0640,
			];
		}

		$this->path = ! empty($config->file['storePath']) ? $config->file['storePath'] : WRITEPATH . 'cache';
		$this->path = rtrim($this->path, '/') . '/';

		if (! is_really_writable($this->path))
		{
			throw CacheException::forUnableToWrite($this->path);
		}

		$this->mode   = $config->file['mode'] ?? 0640;
		$this->prefix = (string) $config->prefix;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes care of any handler-specific setup that must be done.
	 * 负责任何必须完成的特定于处理程序的设置
	 */
	public function initialize()
	{
		// Not to see here...
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to fetch an item from the cache store.
	 * 尝试从缓存存储区获取项
	 *
	 * @param string $key Cache item name
	 *
	 * @return mixed
	 */
	public function get(string $key)
	{
		$key = $this->prefix . $key;

		$data = $this->getItem($key);

		return is_array($data) ? $data['data'] : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Saves an item to the cache store.
	 * 将项保存到缓存存储区
	 *
	 * @param string  $key   Cache item name
	 * @param mixed   $value The data to save
	 * @param integer $ttl   Time To Live, in seconds (default 60)
	 *
	 * @return mixed
	 */
	public function save(string $key, $value, int $ttl = 60)
	{
		$key = $this->prefix . $key;

		$contents = [
			'time' => time(),
			'ttl'  => $ttl,
			'data' => $value,
		];

		if ($this->writeFile($this->path . $key, serialize($contents)))
		{
			chmod($this->path . $key, $this->mode);

			return true;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a specific item from the cache store.
	 * 从高速缓存存储区删除特定项
	 *
	 * @param string $key Cache item name
	 *
	 * @return boolean
	 */
	public function delete(string $key)
	{
		$key = $this->prefix . $key;

		return is_file($this->path . $key) && unlink($this->path . $key);
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic incrementation of a raw stored value.
	 * 对原始存储值执行原子递增
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function increment(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$data = $this->getItem($key);

		if ($data === false)
		{
			$data = [
				'data' => 0,
				'ttl'  => 60,
			];
		}
		elseif (! is_int($data['data']))
		{
			return false;
		}

		$newValue = $data['data'] + $offset;

		return $this->save($key, $newValue, $data['ttl']) ? $newValue : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Performs atomic decrementation of a raw stored value.
	 * 对原始存储值执行原子递减
	 *
	 * @param string  $key    Cache ID
	 * @param integer $offset Step/value to increase by
	 *
	 * @return mixed
	 */
	public function decrement(string $key, int $offset = 1)
	{
		$key = $this->prefix . $key;

		$data = $this->getItem($key);

		if ($data === false)
		{
			$data = [
				'data' => 0,
				'ttl'  => 60,
			];
		}
		elseif (! is_int($data['data']))
		{
			return false;
		}

		$newValue = $data['data'] - $offset;

		return $this->save($key, $newValue, $data['ttl']) ? $newValue : false;
	}

	//--------------------------------------------------------------------

	/**
	 * Will delete all items in the entire cache.
	 * 将删除整个缓存中的所有项
	 *
	 * @return boolean
	 */
	public function clean()
	{
		return $this->deleteFiles($this->path, false, true);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns information on the entire cache.
	 * 返回关于整个缓存的信息。
	 *
	 * The information returned and the structure of the data
	 * varies depending on the handler.
	 * 返回的信息和数据结构因处理程序而异。
	 *
	 * @return mixed
	 */
	public function getCacheInfo()
	{
		return $this->getDirFileInfo($this->path);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns detailed information about the specific item in the cache.
	 * 返回关于缓存中特定项的详细信息
	 *
	 * @param string $key Cache item name.
	 *
	 * @return mixed
	 */
	public function getMetaData(string $key)
	{
		$key = $this->prefix . $key;

		if (! is_file($this->path . $key))
		{
			return false;
		}

		$data = @unserialize(file_get_contents($this->path . $key));

		if (is_array($data))
		{
			$mtime = filemtime($this->path . $key);

			if (! isset($data['ttl']))
			{
				return false;
			}

			return [
				'expire' => $mtime + $data['ttl'],
				'mtime'  => $mtime,
				'data'   => $data['data'],
			];
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines if the driver is supported on this system.
	 * 确定此系统是否支持该驱动程序
	 *
	 * @return boolean
	 */
	public function isSupported(): bool
	{
		return is_writable($this->path);
	}

	//--------------------------------------------------------------------

	/**
	 * Does the heavy lifting of actually retrieving the file and
	 * verifying it's age.
	 * 是否需要进行繁重的文件提取和验证其时间戳的工作
	 *
	 * @param string $key
	 *
	 * @return boolean|mixed
	 */
	protected function getItem(string $key)
	{
		if (! is_file($this->path . $key))
		{
			return false;
		}

		$data = unserialize(file_get_contents($this->path . $key));

		// @phpstan-ignore-next-line
		if ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])
		{
			// If the file is still there then remove it
			if (is_file($this->path . $key))
			{
				unlink($this->path . $key);
			}

			return false;
		}

		return $data;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// SUPPORT METHODS FOR FILES	支持文件的方法
	//--------------------------------------------------------------------

	/**
	 * Writes a file to disk, or returns false if not successful.
	 * 将文件写入磁盘，如果不成功则返回false。
	 *
	 * @param string $path
	 * @param string $data
	 * @param string $mode
	 *
	 * @return boolean
	 */
	protected function writeFile($path, $data, $mode = 'wb')
	{
		if (($fp = @fopen($path, $mode)) === false)
		{
			return false;
		}

		flock($fp, LOCK_EX);

		for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($data, $written))) === false)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return is_int($result);
	}

	//--------------------------------------------------------------------

	/**
	 * Delete Files
	 * 删除文件。
	 *
	 * Deletes all files contained in the supplied directory path.
	 * Files must be writable or owned by the system in order to be deleted.
	 * If the second parameter is set to TRUE, any directories contained
	 * within the supplied base directory will be nuked as well.
	 * 删除指定目录路径中的所有文件。文件必须可写，或由系统拥有，才能被删除。
	 * 如果第二个参数设置为 TRUE，则指定基础目录内的所有子目录也将被清除。
	 *
	 * @param string  $path   File path
	 * @param boolean $delDir Whether to delete any directories found in the path
	 * @param boolean $htdocs Whether to skip deleting .htaccess and index page files
	 * @param integer $_level Current directory depth level (default: 0; internal use only)
	 *
	 * @return boolean
	 */
	protected function deleteFiles(string $path, bool $delDir = false, bool $htdocs = false, int $_level = 0): bool
	{
		// Trim the trailing slash
		$path = rtrim($path, '/\\');

		if (! $currentDir = @opendir($path))
		{
			return false;
		}

		while (false !== ($filename = @readdir($currentDir)))
		{
			if ($filename !== '.' && $filename !== '..')
			{
				if (is_dir($path . DIRECTORY_SEPARATOR . $filename) && $filename[0] !== '.')
				{
					$this->deleteFiles($path . DIRECTORY_SEPARATOR . $filename, $delDir, $htdocs, $_level + 1);
				}
				elseif ($htdocs !== true || ! preg_match('/^(\.htaccess|index\.(html|htm|php)|web\.config)$/i', $filename))
				{
					@unlink($path . DIRECTORY_SEPARATOR . $filename);
				}
			}
		}

		closedir($currentDir);

		return ($delDir === true && $_level > 0) ? @rmdir($path) : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get Directory File Information
	 * 获取目录文件信息。
	 *
	 * Reads the specified directory and builds an array containing the filenames,
	 * filesize, dates, and permissions
	 * 读取指定目录，并构建一个包含文件名、文件大小、日期和权限的数组。
	 *
	 * Any sub-folders contained within the specified path are read as well.
	 *
	 * @param string  $sourceDir    Path to source
	 * @param boolean $topLevelOnly Look only at the top level directory specified?
	 * @param boolean $_recursion   Internal variable to determine recursion status - do not use in calls
	 *
	 * @return array|false
	 */
	protected function getDirFileInfo(string $sourceDir, bool $topLevelOnly = true, bool $_recursion = false)
	{
		static $_filedata = [];
		$relativePath     = $sourceDir;

		if ($fp = @opendir($sourceDir))
		{
			// reset the array and make sure $source_dir has a trailing slash on the initial call
			if ($_recursion === false)
			{
				$_filedata = [];
				$sourceDir = rtrim(realpath($sourceDir) ?: $sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			}

			// Used to be foreach (scandir($source_dir, 1) as $file), but scandir() is simply not as fast
			while (false !== ($file = readdir($fp)))
			{
				if (is_dir($sourceDir . $file) && $file[0] !== '.' && $topLevelOnly === false)
				{
					$this->getDirFileInfo($sourceDir . $file . DIRECTORY_SEPARATOR, $topLevelOnly, true);
				}
				elseif ($file[0] !== '.')
				{
					$_filedata[$file]                  = $this->getFileInfo($sourceDir . $file);
					$_filedata[$file]['relative_path'] = $relativePath;
				}
			}

			closedir($fp);

			return $_filedata;
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Get File Info
	 * 获取文件信息。
	 *
	 * Given a file and path, returns the name, path, size, date modified
	 * Second parameter allows you to explicitly declare what information you want returned
	 * Options are: name, server_path, size, date, readable, writable, executable, fileperms
	 * Returns FALSE if the file cannot be found.
	 *
	 * @param string $file           Path to file
	 * @param mixed  $returnedValues Array or comma separated string of information returned
	 *
	 * @return array|false
	 */
	protected function getFileInfo(string $file, $returnedValues = ['name', 'server_path', 'size', 'date'])
	{
		if (! is_file($file))
		{
			return false;
		}

		if (is_string($returnedValues))
		{
			$returnedValues = explode(',', $returnedValues);
		}

		foreach ($returnedValues as $key)
		{
			switch ($key)
			{
				case 'name':
					$fileInfo['name'] = basename($file);
					break;
				case 'server_path':
					$fileInfo['server_path'] = $file;
					break;
				case 'size':
					$fileInfo['size'] = filesize($file);
					break;
				case 'date':
					$fileInfo['date'] = filemtime($file);
					break;
				case 'readable':
					$fileInfo['readable'] = is_readable($file);
					break;
				case 'writable':
					$fileInfo['writable'] = is_writable($file);
					break;
				case 'executable':
					$fileInfo['executable'] = is_executable($file);
					break;
				case 'fileperms':
					$fileInfo['fileperms'] = fileperms($file);
					break;
			}
		}

		return $fileInfo; // @phpstan-ignore-line
	}

	//--------------------------------------------------------------------
}
