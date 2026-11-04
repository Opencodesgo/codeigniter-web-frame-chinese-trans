<?php
/**
 * CodeIgniter，验证，Validation
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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\View\RendererInterface;
use Config\Validation as ValidationConfig;
use InvalidArgumentException;

/**
 * Validator
 * 验证程序。
 */
class Validation implements ValidationInterface
{
	/**
	 * Files to load with validation functions.
	 * 要用验证函数加载的文件
	 *
	 * @var array
	 */
	protected $ruleSetFiles;

	/**
	 * The loaded instances of our validation files.
	 * 验证文件的加载实例
	 *
	 * @var array
	 */
	protected $ruleSetInstances = [];

	/**
	 * Stores the actual rules that should
	 * be ran against $data.
	 * 存储应应用于 $data 的实际规则
	 *
	 * @var array
	 */
	protected $rules = [];

	/**
	 * The data that should be validated,
	 * where 'key' is the alias, with value.
	 * 需要验证的数据，其中“key”是别名，为值。
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Any generated errors during validation.
	 * 'key' is the alias, 'value' is the message.
	 * 验证过程中生成的任何错误。'key' 是别名，'value' 是消息内容。
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Stores custom error message to use
	 * during validation. Where 'key' is the alias.
	 * 将自定义错误消息存储起来，用于验证时使用。其中 'key' 是别名。
	 *
	 * @var array
	 */
	protected $customErrors = [];

	/**
	 * Our configuration.
	 * 我们的配置
	 *
	 * @var ValidationConfig
	 */
	protected $config;

	/**
	 * The view renderer used to render validation messages.
	 * 用于呈现验证消息的视图呈现器
	 *
	 * @var RendererInterface
	 */
	protected $view;

	//--------------------------------------------------------------------

	/**
	 * Validation constructor.
	 * 验证构造函数
	 *
	 * @param ValidationConfig  $config
	 * @param RendererInterface $view
	 */
	public function __construct($config, RendererInterface $view)
	{
		$this->ruleSetFiles = $config->ruleSets;

		$this->config = $config;

		$this->view = $view;
	}

	//--------------------------------------------------------------------

	/**
	 * Runs the validation process, returning true/false determining whether
	 * validation was successful or not.
	 * 运行验证过程，返回 true/false，以确定验证是否成功。
	 *
	 * @param array  $data    The array of data to validate.
	 * @param string $group   The pre-defined group of rules to apply.
	 * @param string $dbGroup The database group to use.
	 *
	 * @return boolean
	 */
	public function run(array $data = null, string $group = null, string $dbGroup = null): bool
	{
		$data = $data ?? $this->data;

		// i.e. is_unique
		$data['DBGroup'] = $dbGroup;

		$this->loadRuleSets();

		$this->loadRuleGroup($group);

		// If no rules exist, we return false to ensure
		// the developer didn't forget to set the rules.
		// 如果没有规则存在,我们返回false以确保开发人员没有忘记设置规则。
		if (empty($this->rules))
		{
			return false;
		}

		// Replace any placeholders (i.e. {id}) in the rules with
		// the value found in $data, if exists.
		$this->rules = $this->fillPlaceholders($this->rules, $data);

		// Need this for searching arrays in validation.
		helper('array');

		// Run through each rule. If we have any field set for
		// this rule, then we need to run them through!
		foreach ($this->rules as $rField => $rSetup)
		{
			// Blast $rSetup apart, unless it's already an array.
			$rules = $rSetup['rules'] ?? $rSetup;

			if (is_string($rules))
			{
				$rules = $this->splitRules($rules);
			}

			$value          = dot_array_search($rField, $data);
			$fieldNameToken = explode('.', $rField);

			if (is_array($value) && end($fieldNameToken) === '*')
			{
				foreach ($value as $val)
				{
					$this->processRules($rField, $rSetup['label'] ?? $rField, $val ?? null, $rules, $data);
				}
			}
			else
			{
				$this->processRules($rField, $rSetup['label'] ?? $rField, $value ?? null, $rules, $data);
			}
		}

		return ! empty($this->getErrors()) ? false : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Check; runs the validation process, returning true or false
	 * determining whether validation was successful or not.
	 * 检查；执行验证过程，返回 true 或 false，以确定验证是否成功。
	 *
	 * @param mixed    $value  Value to validation.
	 * @param string   $rule   Rule.
	 * @param string[] $errors Errors.
	 *
	 * @return boolean True if valid, else false.
	 */
	public function check($value, string $rule, array $errors = []): bool
	{
		$this->reset();
		$this->setRule('check', null, $rule, $errors);

		return $this->run([
			'check' => $value,
		]);
	}

	//--------------------------------------------------------------------

	/**
	 * Runs all of $rules against $field, until one fails, or
	 * all of them have been processed. If one fails, it adds
	 * the error to $this->errors and moves on to the next,
	 * so that we can collect all of the first errors.
	 * 对 $field 执行所有 $rules，直到其中有一个失败，或所有规则都已处理完毕。
	 *
	 * @param string       $field
	 * @param string|null  $label
	 * @param string|array $value Value to be validated, can be a string or an array
	 * @param array|null   $rules
	 * @param array        $data  All of the fields to check.
	 *
	 * @return boolean
	 */
	protected function processRules(string $field, string $label = null, $value, $rules = null, array $data = null): bool
	{
		if (is_null($data))
		{
			throw new InvalidArgumentException('You must supply the parameter: data.');
		}

		if (in_array('if_exist', $rules, true))
		{
			// If the if_exist rule is defined
			// and the current field does not exist in the input data
			// we can return true, ignoring all other rules to this field.
			// 如果定义了if_存在规则,而当前字段不存在于输入数据中,我们可以返回true,忽略这一字段的所有其他规则。
			if (! array_key_exists($field, array_flatten_with_dots($data)))
			{
				return true;
			}

			// Otherwise remove the if_exist rule and continue the process
			$rules = array_diff($rules, ['if_exist']);
		}

		if (in_array('permit_empty', $rules, true))
		{
			if (! in_array('required', $rules, true) && (is_array($value) ? empty($value) : (trim($value) === '')))
			{
				$passed = true;

				foreach ($rules as $rule)
				{
					if (preg_match('/(.*?)\[(.*)\]/', $rule, $match))
					{
						$rule  = $match[1];
						$param = $match[2];

						if (! in_array($rule, ['required_with', 'required_without'], true))
						{
							continue;
						}

						// Check in our rulesets
						foreach ($this->ruleSetInstances as $set)
						{
							if (! method_exists($set, $rule))
							{
								continue;
							}

							$passed = $passed && $set->$rule($value, $param, $data);
							break;
						}
					}
				}

				if ($passed === true)
				{
					return true;
				}
			}

			$rules = array_diff($rules, ['permit_empty']);
		}

		foreach ($rules as $rule)
		{
			$callable = is_callable($rule);
			$passed   = false;

			// Rules can contain parameters: max_length[5]
			$param = false;
			if (! $callable && preg_match('/(.*?)\[(.*)\]/', $rule, $match))
			{
				$rule  = $match[1];
				$param = $match[2];
			}

			// Placeholder for custom errors from the rules.
			// 从规则中定义自定义错误的占位符
			$error = null;

			// If it's a callable, call and and get out of here.
			if ($callable)
			{
				$passed = $param === false ? $rule($value) : $rule($value, $param, $data);
			}
			else
			{
				$found = false;

				// Check in our rulesets
				foreach ($this->ruleSetInstances as $set)
				{
					if (! method_exists($set, $rule))
					{
						continue;
					}

					$found = true;

					$passed = $param === false ? $set->$rule($value, $error) : $set->$rule($value, $param, $data, $error);
					break;
				}

				// If the rule wasn't found anywhere, we
				// should throw an exception so the developer can find it.
				if (! $found)
				{
					throw ValidationException::forRuleNotFound($rule);
				}
			}

			// Set the error message if we didn't survive.
			if ($passed === false)
			{
				// if the $value is an array, convert it to as string representation
				if (is_array($value))
				{
					$value = '[' . implode(', ', $value) . ']';
				}

				$this->errors[$field] = is_null($error) ? $this->getErrorMessage($rule, $field, $label, $param, $value)
					: $error; // @phpstan-ignore-line

				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a Request object and grabs the input data to use from its
	 * array values.
	 * 接收一个请求对象，并从其数组值中获取用于使用的输入数据。
	 *
	 * @param RequestInterface|IncomingRequest $request
	 *
	 * @return ValidationInterface
	 */
	public function withRequest(RequestInterface $request): ValidationInterface
	{
		if ($request->isJSON())
		{
			$this->data = $request->getJSON(true);
			return $this;
		}

		if (in_array($request->getMethod(), ['put', 'patch', 'delete'], true))
		{
			$this->data = $request->getRawInput();
		}
		else
		{
			$this->data = $request->getVar() ?? [];
		}

		return $this;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Rules	规则
	//--------------------------------------------------------------------

	/**
	 * Sets an individual rule and custom error messages for a single field.
	 * 为单个字段设置单独的规则和自定义错误消息。
	 *
	 * The custom error message should be just the messages that apply to
	 * this field, like so:
	 *
	 *    [
	 *        'rule' => 'message',
	 *        'rule' => 'message'
	 *    ]
	 *
	 * @param string      $field
	 * @param string|null $label
	 * @param string      $rules
	 * @param array       $errors
	 *
	 * @return $this
	 */
	public function setRule(string $field, string $label = null, string $rules, array $errors = [])
	{
		$this->rules[$field] = [
			'label' => $label,
			'rules' => $rules,
		];

		$this->customErrors = array_merge($this->customErrors, [
			$field => $errors,
		]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Stores the rules that should be used to validate the items.
	 * Rules should be an array formatted like:
	 * 存储用于验证项目应使用的规则。规则应为数组格式，例如：
	 *
	 *    [
	 *        'field' => 'rule1|rule2'
	 *    ]
	 *
	 * The $errors array should be formatted like:
	 *    [
	 *        'field' => [
	 *            'rule' => 'message',
	 *            'rule' => 'message
	 *        ],
	 *    ]
	 *
	 * @param array $rules
	 * @param array $errors // An array of custom error messages
	 *
	 * @return ValidationInterface
	 */
	public function setRules(array $rules, array $errors = []): ValidationInterface
	{
		$this->customErrors = $errors;

		foreach ($rules as $field => &$rule)
		{
			if (is_array($rule))
			{
				if (array_key_exists('errors', $rule))
				{
					$this->customErrors[$field] = $rule['errors'];
					unset($rule['errors']);
				}
			}
		}

		$this->rules = $rules;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns all of the rules currently defined.
	 * 返回当前定义的所有规则
	 *
	 * @return array
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the rule for key $field has been set or not.
	 * 检查键$field的规则是否已设置
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public function hasRule(string $field): bool
	{
		return array_key_exists($field, $this->rules);
	}

	//--------------------------------------------------------------------

	/**
	 * Get rule group.
	 * 获取规则组
	 *
	 * @param string $group Group.
	 *
	 * @return string[] Rule group.
	 *
	 * @throws InvalidArgumentException If group not found.
	 */
	public function getRuleGroup(string $group): array
	{
		if (! isset($this->config->$group))
		{
			throw ValidationException::forGroupNotFound($group);
		}

		if (! is_array($this->config->$group))
		{
			throw ValidationException::forGroupNotArray($group);
		}

		return $this->config->$group;
	}

	//--------------------------------------------------------------------

	/**
	 * Set rule group.
	 * 设置规则组
	 *
	 * @param string $group Group.
	 *
	 * @throws InvalidArgumentException If group not found.
	 */
	public function setRuleGroup(string $group)
	{
		$rules = $this->getRuleGroup($group);
		$this->setRules($rules);

		$errorName = $group . '_errors';
		if (isset($this->config->$errorName))
		{
			$this->customErrors = $this->config->$errorName;
		}
	}

	/**
	 * Returns the rendered HTML of the errors as defined in $template.
	 * 返回$template中定义的错误的呈现HTML
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public function listErrors(string $template = 'list'): string
	{
		if (! array_key_exists($template, $this->config->templates))
		{
			throw ValidationException::forInvalidTemplate($template);
		}

		return $this->view->setVar('errors', $this->getErrors())
						->render($this->config->templates[$template]);
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a single error in formatted HTML as defined in the $template view.
	 * 在$template视图中定义的格式化HTML中显示单个错误
	 *
	 * @param string $field
	 * @param string $template
	 *
	 * @return string
	 */
	public function showError(string $field, string $template = 'single'): string
	{
		if (! array_key_exists($field, $this->getErrors()))
		{
			return '';
		}

		if (! array_key_exists($template, $this->config->templates))
		{
			throw ValidationException::forInvalidTemplate($template);
		}

		return $this->view->setVar('error', $this->getError($field))
						->render($this->config->templates[$template]);
	}

	//--------------------------------------------------------------------

	/**
	 * Loads all of the rulesets classes that have been defined in the
	 * Config\Validation and stores them locally so we can use them.
	 */
	protected function loadRuleSets()
	{
		if (empty($this->ruleSetFiles))
		{
			throw ValidationException::forNoRuleSets();
		}

		foreach ($this->ruleSetFiles as $file)
		{
			$this->ruleSetInstances[] = new $file();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Loads custom rule groups (if set) into the current rules.
	 * 将自定义规则组（如果设置了）加载到当前规则中。
	 *
	 * Rules can be pre-defined in Config\Validation and can
	 * be any name, but must all still be an array of the
	 * same format used with setRules(). Additionally, check
	 * for {group}_errors for an array of custom error messages.
	 *
	 * @param string|null $group
	 *
	 * @return array|ValidationException|null
	 */
	public function loadRuleGroup(string $group = null)
	{
		if (empty($group))
		{
			return null;
		}

		if (! isset($this->config->$group))
		{
			throw ValidationException::forGroupNotFound($group);
		}

		if (! is_array($this->config->$group))
		{
			throw ValidationException::forGroupNotArray($group);
		}

		$this->setRules($this->config->$group);

		// If {group}_errors exists in the config file,
		// then override our custom errors with them.
		$errorName = $group . '_errors';

		if (isset($this->config->$errorName))
		{
			$this->customErrors = $this->config->$errorName;
		}

		return $this->rules;
	}

	//--------------------------------------------------------------------

	/**
	 * Replace any placeholders within the rules with the values that
	 * match the 'key' of any properties being set. For example, if
	 * we had the following $data array:
	 *
	 * [ 'id' => 13 ]
	 *
	 * and the following rule:
	 *
	 *  'required|is_unique[users,email,id,{id}]'
	 *
	 * The value of {id} would be replaced with the actual id in the form data:
	 *
	 *  'required|is_unique[users,email,id,13]'
	 *
	 * @param array $rules
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fillPlaceholders(array $rules, array $data): array
	{
		$replacements = [];

		foreach ($data as $key => $value)
		{
			$replacements["{{$key}}"] = $value;
		}

		if (! empty($replacements))
		{
			foreach ($rules as &$rule)
			{
				if (is_array($rule))
				{
					foreach ($rule as &$row)
					{
						// Should only be an `errors` array
						// which doesn't take placeholders.
						if (is_array($row))
						{
							continue;
						}

						$row = strtr($row, $replacements);
					}
					continue;
				}

				$rule = strtr($rule, $replacements);
			}
		}

		return $rules;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Errors	错误
	//--------------------------------------------------------------------

	/**
	 * Checks to see if an error exists for the given field.
	 * 检查给定字段是否存在错误
	 *
	 * @param string $field
	 *
	 * @return boolean
	 */
	public function hasError(string $field): bool
	{
		return array_key_exists($field, $this->getErrors());
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the error(s) for a specified $field (or empty string if not
	 * set).
	 * 返回指定 $field 的错误（如果未设置则返回空字符串）
	 *
	 * @param string $field Field.
	 *
	 * @return string Error(s).
	 */
	public function getError(string $field = null): string
	{
		if ($field === null && count($this->rules) === 1)
		{
			reset($this->rules);
			$field = key($this->rules);
		}

		return array_key_exists($field, $this->getErrors()) ? $this->errors[$field] : '';
	}

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
	 *
	 * Excluded from code coverage because that it always run as cli
	 *
	 * @codeCoverageIgnore
	 */
	public function getErrors(): array
	{
		// If we already have errors, we'll use those.
		// If we don't, check the session to see if any were
		// passed along from a redirect_with_input request.
		if (empty($this->errors) && ! is_cli())
		{
			if (isset($_SESSION, $_SESSION['_ci_validation_errors']))
			{
				$this->errors = unserialize($_SESSION['_ci_validation_errors']);
			}
		}

		return $this->errors ?? [];
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the error for a specific field. Used by custom validation methods.
	 * 设置特定字段的错误。由自定义验证方法使用。
	 *
	 * @param string $field
	 * @param string $error
	 *
	 * @return ValidationInterface
	 */
	public function setError(string $field, string $error): ValidationInterface
	{
		$this->errors[$field] = $error;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to find the appropriate error message
	 * 尝试查找适当的错误消息
	 *
	 * @param string      $rule
	 * @param string      $field
	 * @param string|null $label
	 * @param string      $param
	 * @param string      $value The value that caused the validation to fail.
	 *
	 * @return string
	 */
	protected function getErrorMessage(string $rule, string $field, string $label = null, string $param = null, string $value = null): string
	{
		// Check if custom message has been defined by user
		if (isset($this->customErrors[$field][$rule]))
		{
			$message = lang($this->customErrors[$field][$rule]);
		}
		else
		{
			// Try to grab a localized version of the message...
			// lang() will return the rule name back if not found,
			// so there will always be a string being returned.
			$message = lang('Validation.' . $rule);
		}

		$message = str_replace('{field}', empty($label) ? $field : lang($label), $message);
		$message = str_replace('{param}', empty($this->rules[$param]['label']) ? $param : lang($this->rules[$param]['label']), $message);

		return str_replace('{value}', $value, $message);
	}

	/**
	 * Split rules string by pipe operator.
	 * 按管道操作符拆分规则字符串
	 *
	 * @param string $rules
	 *
	 * @return array
	 */
	protected function splitRules(string $rules): array
	{
		$nonEscapeBracket = '((?<!\\\\)(?:\\\\\\\\)*[\[\]])';
		$pipeNotInBracket = sprintf(
				'/\|(?=(?:[^\[\]]*%s[^\[\]]*%s)*(?![^\[\]]*%s))/',
				$nonEscapeBracket,
				$nonEscapeBracket,
				$nonEscapeBracket
		);

		$_rules = preg_split(
				$pipeNotInBracket,
				$rules
		);

		return array_unique($_rules);
	}

	//--------------------------------------------------------------------
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
	public function reset(): ValidationInterface
	{
		$this->data         = [];
		$this->rules        = [];
		$this->errors       = [];
		$this->customErrors = [];

		return $this;
	}

	//--------------------------------------------------------------------
}
