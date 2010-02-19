<?php

namespace snap;

use snap\loader\NamespaceLoader;

class Loader
{
    static protected $_instance;
    static protected $_autoload = array(__CLASS__, 'load');
    protected $_loader;
    protected $_resLoader;
    static protected $_namespace = 'app';
    static protected $_basePath = '..';

    protected function __construct()
    {
        require_once __DIR__ . '/loader/NamespaceLoader.php';
        $this->_loader = new NamespaceLoader('snap', __DIR__);
        $this->_loader->register();
        $this->_resLoader = new NamespaceLoader(self::$_namespace, self::$_basePath, array(
            'form' => 'forms'
        ));
        $this->_resLoader->register();
    }

    public function getAutoloader()
    {
        return $this->_loader;
    }

    public function getResourceLoader()
    {
        return $this->_resLoader;
    }

    static public function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    static public function setNamespace($namespace)
    {
        self::$_namespace = $namespace;
    }

    static public function setBasePath($basePath)
    {
        self::$_basePath = $basePath;
    }

    static public function addIncludePaths($prepend = null, $append = null)
    {
        $oldPaths = explode(PATH_SEPARATOR, get_include_path());
        $prepend = (array) $prepend;
        $append = (array) $append;
        $merged = array_merge($prepend, $oldPaths, $append);
        $newPaths = array_map('realpath', array_unique($merged));
        set_include_path(implode(PATH_SEPARATOR, $newPaths));
    }

    static public function globalAutoload()
    {
        $globalLoader = new NamespaceLoader();
        $globalLoader->register();
    }

    static public function setAppNamespace($appNs)
    {
        if (null === self::$_instance) {
            self::$_namespace = $appNs;
        } else {
            self::$_instance->getResourceLoader()->setNamespace($appNs);
            return self::$_instance;
        }
    }

    /**
     * @deprecated
     */
    static public function autoload($callback = null)
    {
        $isValidCallback = null !== $callback && is_callable($callback);
        if (null === self::$_instance) {
            $isValidCallback and (self::$_autoload = $callback);
        } else if ($isValidCallback) {
            self::$_autoload = $callback;
            self::$_instance->getAutoloader()
                            ->setDefaultAutoloader($callback);
        }
        return self::getInstance();
    }

    /**
     * @deprecated
     */
    static public function load($class)
    {
        $filename = str_replace(
            array('\\', '_'),
            DIRECTORY_SEPARATOR,
            $class
        ) . '.php';
        return include $filename;
    }

}