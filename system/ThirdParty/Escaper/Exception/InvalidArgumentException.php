<?php
/**
 * Laminas，Escaper，异常，无效参数异常
 */

/**
 * @see       https://github.com/laminas/laminas-escaper for the canonical source repository
 * @copyright https://github.com/laminas/laminas-escaper/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-escaper/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Escaper\Exception;

/**
 * Invalid argument exception
 * 无效参数异常。
 */
class InvalidArgumentException extends \InvalidArgumentException implements
    ExceptionInterface
{
}
