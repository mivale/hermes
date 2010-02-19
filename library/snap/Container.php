<?php

namespace snap;

use snap\app\Resource, snap\Exception;

/**
 * Container
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2009-10 Mon Zafra
 * @package    snap
 * @license    MIT License
 */
class Container extends \ArrayObject
{
    public function __construct($data = array(),
                                $flags = self::ARRAY_AS_PROPS,
                                $iterator = "ArrayIterator")
    {
        $data = array_change_key_case($data, CASE_LOWER);
        parent::__construct($data, $flags, $iterator);
    }

    public function offsetExists($key)
    {
        return true;
    }

    public function offsetSet($key, $val)
    {
        $key = strtolower($key);
        parent::offsetSet($key, $val);
    }

    public function offsetGet($key)
    {
        $key = strtolower($key);
        if (parent::offsetExists($key)) {
            return $this->_get($key);
        }

        // pull values from invoke args and request params if not defined.
        $invokeArgs = parent::offsetExists('invoke')
            ? parent::offsetGet('invoke') : array();
        $reqParams = parent::offsetExists('request')
            ? parent::offsetGet('request')->getParams() : array();

        $pool = array_merge($invokeArgs, $reqParams); // later params overwrite any existing key

        return isset($pool[$key]) ? $pool[$key] : null;
    }

    protected function _get($key)
    {
        $key = strtolower($key);
        $ret = parent::offsetGet($key);
        // if it's a deferred resource, execute it and replace it with its
        // return value
        if ($ret instanceof Resource) {
            $ret = $ret->resolve();
            $this->offsetSet($key, $ret);
        }
        return $ret;
    }

    public function __call($method, $args)
    {
        // init* methods: initialize and return deferred resources
        if ('init' == substr(strtolower($method), 0, 4) && strlen($method) > 4) {
            $key = strtolower(substr($method, 4));
            if (parent::offsetExists($key)) {
                return $this->_get($key);
            } else {
                throw new Exception('No such resource: ' . $key);
            }
        }
        throw new \BadMethodCallException('No such method: ' . $method);
    }


    public function hasParam($key)
    {
        return null !== $this['request']->getParam($key);
    }

    public function getParams()
    {
        return $this['request']->getParams();
    }

    public function getParam($key, $default = null)
    {
        $request = $this['request'];
        $ret = $request->getParam($key);
        return null === $ret ? $default : $ret;
    }

    public function setParam($key, $value)
    {
        $this['request']->setParam($key, $value);
        return $this;
    }

    public function forward($route)
    {
        $front = $this['front'];
        if (is_int($route)) {
            $route = $front->getAliasBySpec($route);
        }
        // TODO: check if route exists and $route != current contextname
        $front->setContextName($route);
    }

    public function redirect($url, $options = array())
    {
        $this['helper']->redirector->gotoUrl($url, $options);
    }

    public function render($script, $name = null)
    {
        $renderer = $this['helper']->viewRenderer;
        $renderer->render($script, $name);
    }
}