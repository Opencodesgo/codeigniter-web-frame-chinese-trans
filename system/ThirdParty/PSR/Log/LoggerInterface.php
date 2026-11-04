<?php namespace Psr\Log;

/**
 * Psr，日志，记录器接口
 */

/**
 * Describes a logger instance
 * 描述记录器实例。
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interface LoggerInterface
{
    /**
     * System is unusable.
	 * 系统不可用
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = []);

    /**
     * Action must be taken immediately.
	 * 必须立即采取行动。
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = []);

    /**
     * Critical conditions.
	 * 临界状态。
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = []);

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
	 * 不需要立即处理，但通常应记录并监控的运行时错误。
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = []);

    /**
     * Exceptional occurrences that are not errors.
	 * 不属于错误的异常情况。
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = []);

    /**
     * Normal but significant events.
	 * 正常但重要的事件
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = []);

    /**
     * Interesting events.
	 * 有趣的事件。
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = []);

    /**
     * Detailed debug information.
	 * 详细的调试信息
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = []);

    /**
     * Logs with an arbitrary level.
	 * 具有任意级别的日志
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = []);
}
