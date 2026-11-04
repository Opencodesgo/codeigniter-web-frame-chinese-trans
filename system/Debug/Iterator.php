<?php
/**
 * CodeIgniter，调试，迭代程序
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use Closure;

/**
 * Iterator for debugging.
 * 调试迭代程序。
 */
class Iterator
{
	/**
	 * Stores the tests that we are to run.
	 * 存储我们要运行的测试
	 *
	 * @var array
	 */
	protected $tests = [];

	/**
	 * Stores the results of each of the tests.
	 * 存储每个测试的结果
	 *
	 * @var array
	 */
	protected $results = [];

	//--------------------------------------------------------------------

	/**
	 * Adds a test to run.
	 * 添加要运行的测试。
	 *
	 * Tests are simply closures that the user can define any sequence of
	 * things to happen during the test.
	 * 测试就是用户可以定义在测试过程中发生任何顺序事件的闭包。
	 *
	 * @param string  $name
	 * @param Closure $closure
	 *
	 * @return $this
	 */
	public function add(string $name, Closure $closure)
	{
		$name = strtolower($name);

		$this->tests[$name] = $closure;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through all of the tests that have been added, recording
	 * time to execute the desired number of iterations, and the approximate
	 * memory usage used during those iterations.
	 * 运行所有已添加的测试，记录执行指定次数迭代所需的时间以及这些迭代期间使用的内存大小。
	 *
	 * @param integer $iterations
	 * @param boolean $output
	 *
	 * @return string|null
	 */
	public function run(int $iterations = 1000, bool $output = true)
	{
		foreach ($this->tests as $name => $test)
		{
			// clear memory before start
			gc_collect_cycles();

			$start    = microtime(true);
			$startMem = $maxMemory = memory_get_usage(true);

			for ($i = 0; $i < $iterations; $i ++)
			{
				$result = $test();

				$maxMemory = max($maxMemory, memory_get_usage(true));

				unset($result);
			}

			$this->results[$name] = [
				'time'   => microtime(true) - $start,
				'memory' => $maxMemory - $startMem,
				'n'      => $iterations,
			];
		}

		if ($output)
		{
			return $this->getReport();
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Get results.
	 * 得到结果
	 *
	 * @return string
	 */
	public function getReport(): string
	{
		if (empty($this->results))
		{
			return 'No results to display.';
		}

		helper('number');

		// Template
		$tpl = '<table>
			<thead>
				<tr>
					<td>Test</td>
					<td>Time</td>
					<td>Memory</td>
				</tr>
			</thead>
			<tbody>
				{rows}
			</tbody>
		</table>';

		$rows = '';

		foreach ($this->results as $name => $result)
		{
			$memory = number_to_size($result['memory'], 4);

			$rows .= "<tr>
				<td>{$name}</td>
				<td>" . number_format($result['time'], 4) . "</td>
				<td>{$memory}</td>
			</tr>";
		}

		$tpl = str_replace('{rows}', $rows, $tpl);

		return $tpl . '<br/>';
	}

	//--------------------------------------------------------------------

}
