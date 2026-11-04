<?php
/**
 * CodeIgniter，视图，表格
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

use CodeIgniter\Database\BaseResult;

/**
 * HTML Table Generating Class
 * HTML表生成类。
 *
 * Lets you create tables manually or from database result objects, or arrays.
 * 允许您手动或从数据库结果对象或数组创建表。
 */
class Table
{
	/**
	 * Data for table rows
	 * 表行数据
	 *
	 * @var array
	 */
	public $rows = [];

	/**
	 * Data for table heading
	 * 表标题数据
	 *
	 * @var array
	 */
	public $heading = [];

	/**
	 * Data for table footing
	 * 台基数据
	 *
	 * @var array
	 */
	public $footing = [];

	/**
	 * Whether or not to automatically create the table header
	 * 是否自动创建表头
	 *
	 * @var boolean
	 */
	public $autoHeading = true;

	/**
	 * Table caption
	 * 表格标题
	 *
	 * @var string|null
	 */
	public $caption;

	/**
	 * Table layout template
	 * 表格布局模板
	 *
	 * @var array
	 */
	public $template;

	/**
	 * Newline setting
	 * 换行符设置
	 *
	 * @var string
	 */
	public $newline = "\n";

	/**
	 * Contents of empty cells
	 * 空单元内容
	 *
	 * @var string
	 */
	public $emptyCells = '';

	/**
	 * Callback for custom table layout
	 * 自定义表布局的回调
	 *
	 * @var callable|null
	 */
	public $function;

	/**
	 * Set the template from the table config file if it exists
	 * 从表配置文件设置模板（如果存在）
	 *
	 * @param  array $config (default: array())
	 * @return void
	 */
	public function __construct($config = [])
	{
		// initialize config
		foreach ($config as $key => $val)
		{
			$this->template[$key] = $val;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the template
	 * 设置模板
	 *
	 * @param  array $template
	 * @return boolean
	 */
	public function setTemplate($template)
	{
		if (! is_array($template))
		{
			return false;
		}

		$this->template = $template;
		return true;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the table heading
	 * 设置表格标题。
	 *
	 * Can be passed as an array or discreet params
	 * 可以作为数组或离散参数传递。
	 *
	 * @return Table
	 */
	public function setHeading()
	{
		$this->heading = $this->_prepArgs(func_get_args());
		return $this;
	}

	/**
	 * Set the table footing
	 * 设置表格页脚。
	 *
	 * Can be passed as an array or discreet params
	 * 可以作为数组或离散参数传递。
	 *
	 * @return Table
	 */
	public function setFooting()
	{
		$this->footing = $this->_prepArgs(func_get_args());
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set columns. Takes a one-dimensional array as input and creates
	 * a multi-dimensional array with a depth equal to the number of
	 * columns. This allows a single array with many elements to be
	 * displayed in a table that has a fixed column count.
	 * 设置列。接受一个一维数组作为输入，创建一个深度等于列数的多维数组。
	 * 这使得具有多个元素的单个数组可以在固定列数的表格中显示。
	 *
	 * @param  array   $array
	 * @param  integer $columnLimit
	 * @return array|false
	 */
	public function makeColumns($array = [], $columnLimit = 0)
	{
		if (! is_array($array) || $array === [] || ! is_int($columnLimit))
		{
			return false;
		}

		// Turn off the auto-heading feature since it's doubtful we
		// will want headings from a one-dimensional array
		// 关闭自动标题功能,因为我们不确定是否需要从一维数组中输入标题。
		$this->autoHeading = false;

		if ($columnLimit === 0)
		{
			return $array;
		}

		$new = [];
		do
		{
			$temp = array_splice($array, 0, $columnLimit);

			if (count($temp) < $columnLimit)
			{
				for ($i = count($temp); $i < $columnLimit; $i++)
				{
					$temp[] = '&nbsp;';
				}
			}

			$new[] = $temp;
		}
		while (count($array) > 0);

		// @phpstan-ignore-next-line
		return $new;
	}

	// --------------------------------------------------------------------

	/**
	 * Set "empty" cells
	 * 设置空单元格。
	 *
	 * Can be passed as an array or discreet params
	 * 可以作为数组或离散参数传递。
	 *
	 * @param  mixed $value
	 * @return Table
	 */
	public function setEmpty($value)
	{
		$this->emptyCells = $value;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add a table row
	 * 添加一个表行。
	 *
	 * Can be passed as an array or discreet params
	 * 可以作为数组或离散参数传递。
	 *
	 * @return Table
	 */
	public function addRow()
	{
		$this->rows[] = $this->_prepArgs(func_get_args());
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Args
	 * 准备参数。
	 *
	 * Ensures a standard associative array format for all cell data
	 * 确保所有单元数据的标准关联数组格式。
	 *
	 * @param  array $args
	 * @return array
	 */
	protected function _prepArgs(array $args)
	{
		// If there is no $args[0], skip this and treat as an associative array
		// This can happen if there is only a single key, for example this is passed to table->generate
		// array(array('foo'=>'bar'))
		if (isset($args[0]) && count($args) === 1 && is_array($args[0]) && ! isset($args[0]['data']))
		{
			$args = $args[0];
		}

		foreach ($args as $key => $val)
		{
			is_array($val) || $args[$key] = ['data' => $val]; // @phpstan-ignore-line
		}

		return $args;
	}

	// --------------------------------------------------------------------

	/**
	 * Add a table caption
	 * 添加表标题
	 *
	 * @param  string $caption
	 * @return Table
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the table
	 * 生成表
	 *
	 * @param mixed $tableData
	 *
	 * @return string
	 */
	public function generate($tableData = null)
	{
		// The table data can optionally be passed to this function
		// either as a database result object or an array
		if (! empty($tableData))
		{
			if ($tableData instanceof BaseResult)
			{
				$this->_setFromDBResult($tableData);
			}
			elseif (is_array($tableData))
			{
				$this->_setFromArray($tableData);
			}
		}

		// Is there anything to display? No? Smite them!
		if (empty($this->heading) && empty($this->rows))
		{
			return 'Undefined table data';
		}

		// Compile and validate the template date
		$this->_compileTemplate();

		// Validate a possibly existing custom cell manipulation function
		if (isset($this->function) && ! is_callable($this->function))
		{
			$this->function = null;
		}

		// Build the table!
		$out = $this->template['table_open'] . $this->newline;

		// Add any caption here
		if ($this->caption)
		{
			$out .= '<caption>' . $this->caption . '</caption>' . $this->newline;
		}

		// Is there a table heading to display?
		if (! empty($this->heading))
		{
			$out .= $this->template['thead_open'] . $this->newline . $this->template['heading_row_start'] . $this->newline;

			foreach ($this->heading as $heading)
			{
				$temp = $this->template['heading_cell_start'];

				foreach ($heading as $key => $val)
				{
					if ($key !== 'data')
					{
						$temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
					}
				}

				$out .= $temp . (isset($heading['data']) ? $heading['data'] : '') . $this->template['heading_cell_end'];
			}

			$out .= $this->template['heading_row_end'] . $this->newline . $this->template['thead_close'] . $this->newline;
		}

		// Build the table rows
		if (! empty($this->rows))
		{
			$out .= $this->template['tbody_open'] . $this->newline;

			$i = 1;

			foreach ($this->rows as $row)
			{
				// We use modulus to alternate the row colors
				$name = fmod($i++, 2) ? '' : 'alt_';

				$out .= $this->template['row_' . $name . 'start'] . $this->newline;

				foreach ($row as $cell)
				{
					$temp = $this->template['cell_' . $name . 'start'];

					foreach ($cell as $key => $val)
					{
						if ($key !== 'data')
						{
							$temp = str_replace('<td', '<td ' . $key . '="' . $val . '"', $temp);
						}
					}

					$cell = isset($cell['data']) ? $cell['data'] : '';
					$out .= $temp;

					if ($cell === '' || $cell === null)
					{
						$out .= $this->emptyCells;
					}
					elseif (isset($this->function))
					{
						$out .= call_user_func($this->function, $cell);
					}
					else
					{
						$out .= $cell;
					}

					$out .= $this->template['cell_' . $name . 'end'];
				}

				$out .= $this->template['row_' . $name . 'end'] . $this->newline;
			}

			$out .= $this->template['tbody_close'] . $this->newline;
		}

		// Any table footing to display?
		if (! empty($this->footing))
		{
			$out .= $this->template['tfoot_open'] . $this->newline . $this->template['footing_row_start'] . $this->newline;

			foreach ($this->footing as $footing)
			{
				$temp = $this->template['footing_cell_start'];

				foreach ($footing as $key => $val)
				{
					if ($key !== 'data')
					{
						$temp = str_replace('<th', '<th ' . $key . '="' . $val . '"', $temp);
					}
				}

				$out .= $temp . (isset($footing['data']) ? $footing['data'] : '') . $this->template['footing_cell_end'];
			}

			$out .= $this->template['footing_row_end'] . $this->newline . $this->template['tfoot_close'] . $this->newline;
		}

		// And finally, close off the table
		$out .= $this->template['table_close'];

		// Clear table class properties before generating the table
		$this->clear();

		return $out;
	}

	// --------------------------------------------------------------------

	/**
	 * Clears the table arrays.  Useful if multiple tables are being generated
	 * 清除表数组。在生成多个表时非常有用。
	 *
	 * @return Table
	 */
	public function clear()
	{
		$this->rows        = [];
		$this->heading     = [];
		$this->footing     = [];
		$this->autoHeading = true;
		$this->caption     = null;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set table data from a database result object
	 * 从数据库结果对象设置表数据
	 *
	 * @param  BaseResult $object Database result object
	 * @return void
	 */
	protected function _setFromDBResult($object)
	{
		// First generate the headings from the table column names
		if ($this->autoHeading === true && empty($this->heading))
		{
			$this->heading = $this->_prepArgs($object->getFieldNames());
		}

		foreach ($object->getResultArray() as $row)
		{
			$this->rows[] = $this->_prepArgs($row);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set table data from an array
	 * 从数组中设置表数据
	 *
	 * @param  array $data
	 * @return void
	 */
	protected function _setFromArray($data)
	{
		if ($this->autoHeading === true && empty($this->heading))
		{
			$this->heading = $this->_prepArgs(array_shift($data));
		}

		foreach ($data as &$row)
		{
			$this->rows[] = $this->_prepArgs($row);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Compile Template
	 * 编译模板文件
	 *
	 * @return void
	 */
	protected function _compileTemplate()
	{
		if ($this->template === null)
		{
			$this->template = $this->_defaultTemplate();
			return;
		}

		$temp = $this->_defaultTemplate();
		foreach (['table_open', 'thead_open', 'thead_close', 'heading_row_start', 'heading_row_end', 'heading_cell_start', 'heading_cell_end', 'tbody_open', 'tbody_close', 'row_start', 'row_end', 'cell_start', 'cell_end', 'row_alt_start', 'row_alt_end', 'cell_alt_start', 'cell_alt_end', 'table_close'] as $val)
		{
			if (! isset($this->template[$val]))
			{
				$this->template[$val] = $temp[$val];
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Default Template
	 * 默认模板
	 *
	 * @return array
	 */
	protected function _defaultTemplate()
	{
		return [
			'table_open'         => '<table border="0" cellpadding="4" cellspacing="0">',
			'thead_open'         => '<thead>',
			'thead_close'        => '</thead>',
			'heading_row_start'  => '<tr>',
			'heading_row_end'    => '</tr>',
			'heading_cell_start' => '<th>',
			'heading_cell_end'   => '</th>',
			'tfoot_open'         => '<tfoot>',
			'tfoot_close'        => '</tfoot>',
			'footing_row_start'  => '<tr>',
			'footing_row_end'    => '</tr>',
			'footing_cell_start' => '<td>',
			'footing_cell_end'   => '</td>',
			'tbody_open'         => '<tbody>',
			'tbody_close'        => '</tbody>',
			'row_start'          => '<tr>',
			'row_end'            => '</tr>',
			'cell_start'         => '<td>',
			'cell_end'           => '</td>',
			'row_alt_start'      => '<tr>',
			'row_alt_end'        => '</tr>',
			'cell_alt_start'     => '<td>',
			'cell_alt_end'       => '</td>',
			'table_close'        => '</table>',
		];
	}

	// --------------------------------------------------------------------
}
