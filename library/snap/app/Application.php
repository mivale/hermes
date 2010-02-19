<?php

namespace snap\app;

use snap\Front, snap\Container, snap\Exception;

/**
 * Description of Application
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2010 Mon Zafra
 * @package    snap
 * @license    MIT License
 */
class Application
{
    const DEFAULT_ENV = 'production';
    protected $_bootstraps = array();
    protected $_container;

    public function __construct()
    {
        // TODO: get rid of Front singleton
        $this->front = Front::getInstance();
    }

    public function getContainer()
    {
        if (null === $this->_container) {
            $this->_container = new Container();
        }
        return $this->_container;
    }

    public function __get($key)
    {
        return $this->getContainer()->$key;
    }

    public function __set($key, $val)
    {
        $this->getContainer()->$key = $val;
    }

    public function __invoke($name = null, $parent = null)
    {
        return $this->setBootstrap($name, $parent);
    }

    public function setBootstrap($name = null, $parent = null)
    {
        if (empty($name)) {
            $name = static::DEFAULT_ENV;
        }
        $name = strtolower($name);
        
        if (null !== $parent) {
            $parent = strtolower($parent);
            if ($parent != $name && !empty($this->_bootstraps[$parent])) {
                $parent = $this->_bootstraps[$parent];
            } else {
                throw new Exception('Invalid parent specified.');
            }
        }

        $env = new Bootstrap($this->getContainer(), $parent);
        if (null !== $name) {
            $this->_bootstraps[$name] = $env;
        } else {
            $this->_bootstraps[] = $env;
        }

        return $env;
    }

    public function getBootstrap($name)
    {
        $name = strtolower($name);
        if (!isset($this->_bootstraps[$name])) {
            throw new Exception('No such bootstrap: ' . $name);
        }
        return $this->_bootstraps[$name];
    }

    public function bootstrap($env)
    {
        $bootstrap = $this->getBootstrap($env);
        $config = $bootstrap->execute();

        $this->front->setContainer($this->getContainer());
        $this->front->setParams($config);
        return $this;
    }

    public function run($env)
    {
        $this->bootstrap($env);
        return $this->front->dispatch();
    }

}