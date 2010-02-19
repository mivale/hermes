<?php

namespace snap;

use snap\Front, snap\Loader, snap\app\Application;

/**
 * Facade class
 *
 * @author    Mon Zafra <monzee at gmail>
 * @copyright (c)2009-10 Mon Zafra
 * @package   snap
 * @license   MIT License
 */
abstract class Snap
{
    static protected $_app;
    
    static public function __callStatic($methodRaw, $args)
    {
        // Front::route() facade
        $method = strtolower($methodRaw);
        $validTypes = array('get', 'post', 'put', 'delete', 'head', 'options',
            'flash', 'ajax', 'action');
        if (in_array($method, $validTypes)) {
            $front = self::Application()->front;
            $context = call_user_func_array($front, $args);
            return $context->setCallbackType($method);
        }

        // config stuff
        $method = 'set' . $methodRaw;
        if (method_exists(__CLASS__, $method)) {
            call_user_func_array(array(__CLASS__, $method), $args);
        } else {
            $val = empty($args) ? null : $args[0];
            self::set($methodRaw, $val);
        }
    }

    static public function registerGlobals()
    {
        Globals::register();
    }

    static public function autoload($prepend = null, $append = null, $callback = null)
    {
        require_once __DIR__ . '/Loader.php';
        if (!empty($prepend) || !empty($append)) {
            Loader::addIncludePaths($prepend, $append);
        }
        return Loader::getInstance();
//        return Loader::autoload($callback);
    }

    static public function run($env = 'production')
    {
        return self::Application()->run($env);
    }

    static public function option($key)
    {
        $args = func_get_args();
        if (1 == count($args)) {
            return self::Application()->front->getParam($key);
        } else {
            self::set($key, $args[1]);
        }
    }

    static public function set($key, $value = null)
    {
        self::Application()->front->setParam($key, $value);
    }

    static public function setLayout($script = null, $path = null)
    {
        // TODO: use zend_application resource plugins instead

        // if this isn't called here, Zend_Controller_Front is invoked by zend_layout
        $front = self::Application()->front;

        $layout = \Zend_Layout::getMvcInstance();
        if (null === $layout) {
            $layout = \Zend_Layout::startMvc();
        }

        if (is_bool($script)) {
            $method = $script ? 'enableLayout' : 'disableLayout';
            $layout->$method();
        } else {
            $config = $front->getParams();
            $script = $script ?: $config['layout'];
            $path = $path ?: (isset($config['layoutPath'])
                ? $config['layoutPath'] : $config['viewPath']);

            $layout->setLayout($script)
                   ->setLayoutPath($path);
        }
    }

    static public function Front()
    {
        return self::Application()->front;
    }

    static public function Application()
    {
        if (null === self::$_app) {
            self::$_app = new Application();
        }
        return self::$_app;
    }

    static public function setup($env = null, $parent = null)
    {
        $app = self::Application();
        return $app($env, $parent);
    }

}
