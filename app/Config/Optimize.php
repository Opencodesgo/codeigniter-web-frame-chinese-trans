<?php
/**
 * App，配置，优化
 */

namespace Config;

/**
 * Optimization Configuration. 	优化配置
 *
 * NOTE: This class does not extend BaseConfig for performance reasons.
 *       So you cannot replace the property values with Environment Variables.
 *
 * @immutable
 */
class Optimize
{
    /**
     * --------------------------------------------------------------------------
     * Config Caching
     * --------------------------------------------------------------------------
     *
     * @see https://codeigniter.com/user_guide/concepts/factories.html#config-caching
     */
    public bool $configCacheEnabled = false;

    /**
     * --------------------------------------------------------------------------
     * Config Caching
     * --------------------------------------------------------------------------
     *
     * @see https://codeigniter.com/user_guide/concepts/autoloader.html#file-locator-caching
     */
    public bool $locatorCacheEnabled = false;
}
