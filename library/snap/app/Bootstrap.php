<?php

namespace snap\app;

use snap\Exception;

/**
 * Bootstrap
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2010 Mon Zafra
 * @package    snap
 * @license    MIT License
 */
class Bootstrap
{
    protected $_container;
    protected $_parent;
    protected $_callbacks = array();

    public function __construct($container, $parent = null)
    {
        $this->_container = $container;
        $this->_parent = $parent;
    }

    public function __call($method, $args)
    {
        if (empty($args[0]) || !is_callable($args[0])) {
            throw new Exception('Argument must be a callback.');
        }
        $resource = strtolower($method);
        $this->_callbacks[$resource] = $args[0];
        return $this;
    }

    public function getResources()
    {
        // if i have a parent, get parent's callbacks
        // append my callbacks
        // if a parent callback is overridden
        //     create a new callback that wraps it and then
        //     pass it to my callback as the first arg
        if (null === $this->_parent) {
            $callbacks = $this->_callbacks;
        } else {
            $callbacks = $this->_parent->getResources();
            $parentKeys = array_keys($callbacks);
            foreach ($this->_callbacks as $key => $val) {
                if (in_array($key, $parentKeys)) {
                    $callbacks[$key] = $this->_wrapParent($key);
                } else {
                    $callbacks[$key] = $val;
                }
            }
        }

        return $callbacks;
    }

    public function getResource($key)
    {
        $key = strtolower($key);
        if (!isset($this->_callbacks[$key])) {
            throw new Exception('No such resource: ' . $key);
        }
        return $this->_callbacks[$key];
    }


    public function hasResource($resource)
    {
        $resource = strtolower($resource);
        return array_key_exists($resource, $this->_callbacks);
    }

    public function execute($resource = null)
    {
        if (null === $resource) {
            $callbacks = $this->getResources();
        } else {
            $resource = strtolower($resource);
            $callbacks = array();
            if (null !== $this->_parent) {
                $callbacks[$resource] = $this->_wrapParent($resource);
            } else {
                $callbacks[$resource] = $this->getResource($resource);
            }
        }

        foreach ($callbacks as $key => $callback) {
            if ('init' == substr($key, 0, 4)) {
                $key = substr($key, 4);
                $ret = $callback();
            } else {
                $ret = new Resource($callback);
            }

            if (empty($key)) {
                // just executed the 'init' method;
                $config = null !== $ret ? $ret : array();
            } else if (null !== $ret) {
                $this->_container->$key = $ret;
            }
        }

        return null === $resource ? $config : $ret;
    }

    protected function _wrapParent($key)
    {
        $parent = $this->_parent;
        $childCallback = $this->getResource($key);
        return function () use ($key, $parent, $childCallback) {
            $super = function () use ($key, $parent) {
                return $parent->execute($key);
            };
            return $childCallback($super);
        };
    }

}