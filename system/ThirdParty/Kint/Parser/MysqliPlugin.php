<?php
/**
 * Kint，解析器，Mysqli 插件
 */

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 Jonathan Vollebregt (jnvsor@gmail.com), Rokas Šleinius (raveren@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Kint\Parser;

use Kint\Object\BasicObject;
use Mysqli;

/**
 * Adds support for Mysqli object parsing.
 * 增加了对mysql对象解析的支持。
 *
 * Due to the way mysqli is implemented in PHP, this will cause
 * warnings on certain Mysqli objects if screaming is enabled.
 * 由于 mysqli 在 PHP 中的实现方式，如果启用了“screaming”（高亮显示），
 * 这将导致某些 mysqli 对象出现警告。
 */
class MysqliPlugin extends Plugin
{
    // These 'properties' are actually globals
	// 这些“属性”实际上是全局的
    protected $always_readable = array(
        'client_version' => true,
        'connect_errno' => true,
        'connect_error' => true,
    );

    // These are readable on empty mysqli objects, but not on failed connections
	// 这些在空的mysqli对象上是可读的，但在失败的连接上是不可读的。
    protected $empty_readable = array(
        'client_info' => true,
        'errno' => true,
        'error' => true,
    );

    // These are only readable on connected mysqli objects
	// 这些只能在连接的mysqli对象上读取
    protected $connected_readable = array(
        'affected_rows' => true,
        'error_list' => true,
        'field_count' => true,
        'host_info' => true,
        'info' => true,
        'insert_id' => true,
        'server_info' => true,
        'server_version' => true,
        'stat' => true,
        'sqlstate' => true,
        'protocol_version' => true,
        'thread_id' => true,
        'warning_count' => true,
    );

    public function getTypes()
    {
        return array('object');
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_COMPLETE;
    }

    public function parse(&$var, BasicObject &$o, $trigger)
    {
        if (!$var instanceof Mysqli) {
            return;
        }

        $connected = false;
        $empty = false;

        if (\is_string(@$var->sqlstate)) {
            $connected = true;
        } elseif (\is_string(@$var->client_info)) {
            $empty = true;
        }

        foreach ($o->value->contents as $key => $obj) {
            if (isset($this->connected_readable[$obj->name])) {
                if (!$connected) {
                    continue;
                }
            } elseif (isset($this->empty_readable[$obj->name])) {
                if (!$connected && !$empty) {
                    continue;
                }
            } elseif (!isset($this->always_readable[$obj->name])) {
                continue;
            }

            if ('null' !== $obj->type) {
                continue;
            }

            $param = $var->{$obj->name};

            if (null === $param) {
                continue;
            }

            $base = BasicObject::blank($obj->name, $obj->access_path);

            $base->depth = $obj->depth;
            $base->owner_class = $obj->owner_class;
            $base->operator = $obj->operator;
            $base->access = $obj->access;
            $base->reference = $obj->reference;

            $o->value->contents[$key] = $this->parser->parse($param, $base);
        }
    }
}
