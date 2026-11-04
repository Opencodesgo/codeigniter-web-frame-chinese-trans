<?php
/**
 * CodeIgniter，验证，验证接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\RequestInterface;

/**
 * Expected behavior of a validator
 * 验证器的预期行为。
 */
interface ValidationInterface
{
	/**
	 * Runs the validation process, returning true/false determining whether
	 * or not validation was successful.
	 * 运行验证过程，返回 true/false，以确定验证是否成功。
	 *
	 * @param array  $data  The array of data to validate.
	 * @param string $group The pre-defined group of rules to apply.
	 *
	 * @return boolean
	 */
	public function run(array $data = null, string $group = null): bool;

	//--------------------------------------------------------------------

	/**
	 * Check; runs the validation process, returning true or false
	 * determining whether or not validation was successful.
	 * 检查；执行验证过程，返回 true 或 false，以确定验证是否成功。
	 *
	 * @param mixed    $value  Value to validation.
	 * @param string   $rule   Rule.
	 * @param string[] $errors Errors.
	 *
	 * @return boolean True if valid, else false.
	 */
	public function check($value, string $rule, array $errors = []): bool;

	//--------------------------------------------------------------------

	/**
	 * Takes a Request object and grabs the input data to use from its
	 * array values.
	 * 接收一个请求对象，并从其数组值中获取用于使用的输入数据。
	 *
	 * @param RequestInterface $request
	 *
	 * @return ValidationInterface
	 */
	public function withRequest(RequestInterface $request): ValidationInterface;

	//--------------------------------------------------------------------
	// Rules	规则
	//--------------------------------------------------------------------

	/**
	 * Stores the rules that should be used to validate the items.
	 * 存储应用于验证项的规则
	 *
	 * @param array $rules
	 * @param array $messages
	 *
	 * @return ValidationInterface
	 */
	public function setRules(array $rules, array $messages = []): ValidationInterface;

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the rule for key $field has been set or not.
	 * 检查键$field的规则是否已设置
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public function hasRule(string $field): bool;

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Errors
	//--------------------------------------------------------------------

	/**
	 * Returns the error for a specified $field (or empty string if not set).
	 * 返回指定$字段的错误（如果没有设置则为空字符串）
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public function getError(string $field): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the array of errors that were encountered during
	 * a run() call. The array should be in the following format:
	 * 返回在调用 run() 时遇到的错误数组。该数组应按以下格式排列：
	 *
	 *    [
	 *        'field1' => 'error message',
	 *        'field2' => 'error message',
	 *    ]
	 *
	 * @return array
	 */
	public function getErrors(): array;

	//--------------------------------------------------------------------

	/**
	 * Sets the error for a specific field. Used by custom validation methods.
	 * 设置特定字段的错误。由自定义验证方法使用。
	 *
	 * @param string $alias
	 * @param string $error
	 *
	 * @return ValidationInterface
	 */
	public function setError(string $alias, string $error): ValidationInterface;

	//--------------------------------------------------------------------
	// Misc	混杂的
	//--------------------------------------------------------------------

	/**
	 * Resets the class to a blank slate. Should be called whenever
	 * you need to process more than one array.
	 * 将类重置为空白状态。每当需要处理多个数组时，都应调用此方法。
	 *
	 * @return ValidationInterface
	 */
	public function reset(): ValidationInterface;

	//--------------------------------------------------------------------
}
