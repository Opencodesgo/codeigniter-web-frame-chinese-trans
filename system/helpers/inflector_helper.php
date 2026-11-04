<?php
/**
 * System，帮助，偏转器助手
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// --------------------------------------------------------------------

/**
 * CodeIgniter Inflector Helpers
 * 偏转器助手。
 */
if (! function_exists('singular'))
{
	/**
	 * Singular
	 * 单一的。
	 *
	 * Takes a plural word and makes it singular
	 * 把一个复数单词变成单数。
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */
	function singular(string $string): string
	{
		$result = $string;

		if (! is_pluralizable($result))
		{
			return $result;
		}

		//Arranged in order.
		$singularRules = [
			'/(matr)ices$/'                                                   => '\1ix',
			'/(vert|ind)ices$/'                                               => '\1ex',
			'/^(ox)en/'                                                       => '\1',
			'/(alias)es$/'                                                    => '\1',
			'/([octop|vir])i$/'                                               => '\1us',
			'/(cris|ax|test)es$/'                                             => '\1is',
			'/(shoe)s$/'                                                      => '\1',
			'/(o)es$/'                                                        => '\1',
			'/(bus|campus)es$/'                                               => '\1',
			'/([m|l])ice$/'                                                   => '\1ouse',
			'/(x|ch|ss|sh)es$/'                                               => '\1',
			'/(m)ovies$/'                                                     => '\1\2ovie',
			'/(s)eries$/'                                                     => '\1\2eries',
			'/([^aeiouy]|qu)ies$/'                                            => '\1y',
			'/([lr])ves$/'                                                    => '\1f',
			'/(tive)s$/'                                                      => '\1',
			'/(hive)s$/'                                                      => '\1',
			'/([^f])ves$/'                                                    => '\1fe',
			'/(^analy)ses$/'                                                  => '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
			'/([ti])a$/'                                                      => '\1um',
			'/(p)eople$/'                                                     => '\1\2erson',
			'/(m)en$/'                                                        => '\1an',
			'/(s)tatuses$/'                                                   => '\1\2tatus',
			'/(c)hildren$/'                                                   => '\1\2hild',
			'/(n)ews$/'                                                       => '\1\2ews',
			'/(quiz)zes$/'                                                    => '\1',
			'/([^us])s$/'                                                     => '\1',
		];

		foreach ($singularRules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}
}

//--------------------------------------------------------------------

if (! function_exists('plural'))
{
	/**
	 * Plural
	 * 复数。
	 *
	 * Takes a singular word and makes it plural
	 * 把一个单数单词变成复数。
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */
	function plural(string $string): string
	{
		$result = $string;

		if (! is_pluralizable($result))
		{
			return $result;
		}

		$pluralRules = [
			'/(quiz)$/'               => '\1zes',    // quizzes
			'/^(ox)$/'                => '\1\2en', // ox
			'/([m|l])ouse$/'          => '\1ice', // mouse, louse
			'/(matr|vert|ind)ix|ex$/' => '\1ices', // matrix, vertex, index
			'/(x|ch|ss|sh)$/'         => '\1es', // search, switch, fix, box, process, address
			'/([^aeiouy]|qu)y$/'      => '\1ies', // query, ability, agency
			'/(hive)$/'               => '\1s', // archive, hive
			'/(?:([^f])fe|([lr])f)$/' => '\1\2ves', // half, safe, wife
			'/sis$/'                  => 'ses', // basis, diagnosis
			'/([ti])um$/'             => '\1a', // datum, medium
			'/(p)erson$/'             => '\1eople', // person, salesperson
			'/(m)an$/'                => '\1en', // man, woman, spokesman
			'/(c)hild$/'              => '\1hildren', // child
			'/(buffal|tomat)o$/'      => '\1\2oes', // buffalo, tomato
			'/(bu|campu)s$/'          => '\1\2ses', // bus, campus
			'/(alias|status|virus)$/' => '\1es', // alias
			'/(octop)us$/'            => '\1i', // octopus
			'/(ax|cris|test)is$/'     => '\1es', // axis, crisis
			'/s$/'                    => 's', // no change (compatibility)
			'/$/'                     => 's',
		];

		foreach ($pluralRules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}
}

//--------------------------------------------------------------------

if (! function_exists('counted'))
{
	/**
	 * Counted
	 * 计算。
	 *
	 * Takes a number and a word to return the plural or not
	 * E.g. 0 cats, 1 cat, 2 cats, ...
	 *
	 * @param integer $count  Number of items
	 * @param string  $string Input string
	 *
	 * @return string
	 */
	function counted(int $count, string $string): string
	{
		$result = "{$count} ";

		return $result . ($count === 1 ? singular($string) : plural($string));
	}
}

//--------------------------------------------------------------------

if (! function_exists('camelize'))
{
	/**
	 * Camelize
	 * 驼峰格式。
	 *
	 * Takes multiple words separated by spaces or
	 * underscores and converts them to camel case.
	 * 将用空格或下划线分隔的多个单词转换为驼峰命名法。
	 *
	 * @param  string $string Input string
	 * @return string
	 */
	function camelize(string $string): string
	{
		return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[\s_]+/', ' ', $string))));
	}
}

//--------------------------------------------------------------------

if (! function_exists('pascalize'))
{
	/**
	 * Pascalize
	 * 帕斯卡。
	 *
	 * Takes multiple words separated by spaces or
	 * underscores and converts them to Pascal case,
	 * which is camel case with an uppercase first letter.
	 * 将用空格或下划线分隔的多个单词转换为帕斯卡大小写，即首字母大写的驼峰命名法。
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */
	function pascalize(string $string): string
	{
		return ucfirst(camelize($string));
	}
}

//--------------------------------------------------------------------

if (! function_exists('underscore'))
{
	/**
	 * Underscore
	 * 下划线。
	 *
	 * Takes multiple words separated by spaces and underscores them
	 * 接受以空格分隔的多个单词并下划线。
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */
	function underscore(string $string): string
	{
		$replacement = trim($string);

		return preg_replace('/[\s]+/', '_', $replacement);
	}
}

//--------------------------------------------------------------------

if (! function_exists('humanize'))
{
	/**
	 * Humanize
	 * 人性化。
	 *
	 * Takes multiple words separated by the separator,
	 * camelizes and changes them to spaces
	 * 将多个用分隔符分隔的单词进行驼峰命名，并将其转换为空格。
	 *
	 * @param string $string    Input string
	 * @param string $separator Input separator
	 *
	 * @return string
	 */
	function humanize(string $string, string $separator = '_'): string
	{
		$replacement = trim($string);

		return ucwords
				(
				preg_replace('/[' . $separator . ']+/', ' ', $replacement)
		);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('is_pluralizable'))
{
	/**
	 * Checks if the given word has a plural version.
	 * 检查给定的单词是否有复数形式
	 *
	 * @param string $word Word to check
	 *
	 * @return boolean
	 */
	function is_pluralizable(string $word): bool
	{
		$uncountables = in_array
				(
			strtolower($word), [
						   'advice',
						   'bravery',
						   'butter',
						   'chaos',
						   'clarity',
						   'coal',
						   'courage',
						   'cowardice',
						   'curiosity',
						   'education',
						   'equipment',
						   'evidence',
						   'fish',
						   'fun',
						   'furniture',
						   'greed',
						   'help',
						   'homework',
						   'honesty',
						   'information',
						   'insurance',
						   'jewelry',
						   'knowledge',
						   'livestock',
						   'love',
						   'luck',
						   'marketing',
						   'meta',
						   'money',
						   'mud',
						   'news',
						   'patriotism',
						   'racism',
						   'rice',
						   'satisfaction',
						   'scenery',
						   'series',
						   'sexism',
						   'silence',
						   'species',
						   'spelling',
						   'sugar',
						   'water',
						   'weather',
						   'wisdom',
						   'work',
					   ], true);

		return ! $uncountables;
	}
}

// ------------------------------------------------------------------------

if (! function_exists('dasherize'))
{
	/**
	 * Replaces underscores with dashes in the string.
	 * 将字符串中的下划线替换为破折号
	 *
	 * @param string $string Input string
	 *
	 * @return string
	 */
	function dasherize(string $string): string
	{
		return str_replace('_', '-', $string);
	}
}

// ------------------------------------------------------------------------

if (! function_exists('ordinal'))
{
	/**
	 * Returns the suffix that should be added to a
	 * number to denote the position in an ordered
	 * sequence such as 1st, 2nd, 3rd, 4th.
	 * 返回应添加在数字后以表示其在有序序列中位置的后缀，例如第1位、第2位、第3位、第4位。
	 *
	 * @param integer $integer The integer to determine the suffix
	 *
	 * @return string
	 */
	function ordinal(int $integer): string
	{
		$suffixes = [
			'th',
			'st',
			'nd',
			'rd',
			'th',
			'th',
			'th',
			'th',
			'th',
			'th',
		];

		return $integer % 100 >= 11 && $integer % 100 <= 13 ? 'th' : $suffixes[$integer % 10];
	}
}

// ------------------------------------------------------------------------

if (! function_exists('ordinalize'))
{
	/**
	 * Turns a number into an ordinal string used
	 * to denote the position in an ordered sequence
	 * such as 1st, 2nd, 3rd, 4th.
	 * 将数字转换为序数字符串，用于表示有序序列中的位置，例如第1位、第2位、第3位、第4位。
	 *
	 * @param integer $integer The integer to ordinalize
	 *
	 * @return string
	 */
	function ordinalize(int $integer): string
	{
		return $integer . ordinal($integer);
	}
}
