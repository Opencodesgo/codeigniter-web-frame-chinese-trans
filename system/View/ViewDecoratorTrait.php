<?php

/**
 * This file is part of CodeIgniter 4 framework.
 * 该文件是CodeIgniter4框架的一部分
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\View;

use CodeIgniter\View\Exceptions\ViewException;
use Config\View as ViewConfig;

trait ViewDecoratorTrait
{
    /**
     * Runs the generated output through any declared
     * view decorators.
	 * 通过任何声明的运行生成的输出视图装饰器
     */
    protected function decorateOutput(string $html): string
    {
        $decorators = $this->config->decorators ?? config(ViewConfig::class)->decorators;

        foreach ($decorators as $decorator) {
            if (! is_subclass_of($decorator, ViewDecoratorInterface::class)) {
                throw ViewException::forInvalidDecorator($decorator);
            }

            $html = $decorator::decorate($html);
        }

        return $html;
    }
}
