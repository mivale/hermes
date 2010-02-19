<?php

namespace snap\app;

use snap\Exception;

/**
 * Description of Resource
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2010 Mon Zafra
 * @license    MIT License
 */
class Resource
{
    protected $_callback;

    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Argument must be a callback');
        }
        $this->_callback = $callback;
    }

    public function resolve()
    {
        return call_user_func($this->_callback);
    }

}