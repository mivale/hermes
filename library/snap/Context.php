<?php

namespace snap;

class Context
{
    protected $_route;
    protected $_callbacks = array();
    protected $_viewScript;
    protected $_conditions = array();
    protected $_contextKey;
    protected $_type = 'action';

    public function __construct($route = null, $contextKey = 'context')
    {
        $this->_route = $route;
        $this->_contextKey = $contextKey;
    }

    public function __call($method, $args)
    {
        $normalized = strtolower($method);
        $aliasMap = array(
            'default' => 'setDefault',
            'do' => 'type',
            'defaults' => 'addDefaults',
            'require' => 'setRequirement',
            'render' => 'view',
            'map' => 'addMapping',
        );
        if (isset($aliasMap[$normalized])) {
            return call_user_func_array(
                array($this, $aliasMap[$normalized]),
                $args
            );
        }
        throw new \BadMethodCallException('No such method (' . $method . ').');
    }

    public function setRoute($route)
    {
        $this->_route = $route;
        return $this;
    }

    public function setDefault($key, $value)
    {
        if ($key == $this->_contextKey) {
            throw new Exception('The key "' . $this->_contextKey . '" is reserved. You cannot set a default value for it.');
        }
        if (method_exists($this->_route, 'setDefault')) {
            $this->_route->setDefault($key, $value);
        }
        return $this;
    }

    public function addDefaults($defaults)
    {
        foreach ($defaults as $key => $value) {
            $this->setDefault($key, $value);
        }
        return $this;
    }

    public function setRequirement($key, $value)
    {
        if ($key == $this->_contextKey) {
            throw new Exception('The key "' . $this->_contextKey . '" is reserved. You cannot set a requirement for it.');
        }
        if (method_exists($this->_route, 'setRequirement')) {
            $this->_route->setRequirement($key, $value);
        }
        return $this;
    }

    public function addRequirements($requirements)
    {
        foreach ($requirements as $key => $value) {
            $this->setRequirement($key, $value);
        }
        return $this;
    }

    public function addMapping($key, $value)
    {
        if (method_exists($this->_route, 'map')) {
            $this->_route->map($key, $value);
        }
        return $this;
    }

    public function setMap($map)
    {
        if (method_exists($this->_route, 'setMap')) {
            $this->_route->setMap($map);
        }
        return $this;
    }

    public function setCallback($type, $callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Invalid callback specified.');
        }
        $type = strtolower($type);
        $this->_callbacks[$type] = $callback;
        return $this;
    }

    public function setCallbackType($type)
    {
        $this->_type = $type;
        return $this;
    }

    public function type($callback)
    {
        return $this->setCallback($this->_type, $callback);
    }

    public function action($callback)
    {
        return $this->setCallback('action', $callback);
    }

    public function get($callback)
    {
        return $this->setCallback('get', $callback);
    }

    public function post($callback)
    {
        return $this->setCallback('post', $callback);
    }

    public function put($callback)
    {
        return $this->setCallback('put', $callback);
    }

    public function delete($callback)
    {
        return $this->setCallback('delete', $callback);
    }

    public function head($callback)
    {
        return $this->setCallback('head', $callback);
    }

    public function options($callback)
    {
        return $this->setCallback('options', $callback);
    }

    public function ajax($callback)
    {
        return $this->setCallback('xmlHttpRequest', $callback);
    }

    public function flash($callback)
    {
        return $this->setCallback('flashRequest', $callback);
    }

    public function view($script)
    {
        $this->_viewScript = $script;
        return $this;
    }

    public function hasCallback($type)
    {
        $type = strtolower($type);
        return isset($this->_callbacks[$type]);
    }

    public function hasView()
    {
        return null !== $this->_viewScript;
    }

    public function getViewScript()
    {
        return $this->_viewScript;
    }

    public function call($type, $params)
    {
        $type = strtolower($type);
        if (!$this->hasCallback($type)) {
            return;
        }
        return call_user_func_array($this->_callbacks[$type], $params);
    }

    public function isDispatchable(\Zend_Controller_Request_Http $request)
    {
        $dispatchable = true;

        if (!$this->hasCallback('action') && !$this->hasView()) {
            $method = strtolower($request->getMethod());
            if (!$this->hasCallback($method)) {
                $dispatchable = false;
            }
            $types = array('flashRequest', 'xmlHttpRequest');
            while ($dispatchable && ($type = current($types))) {
                $isType = 'is' . ucfirst($type);
                if ($request->$isType() && !$this->hasCallback($type)) {
                    $dispatchable = false;
                }
                next($types);
            }
        }

        // TODO: implement this
        reset($this->_conditions);
        while ($dispatchable && ($elem = each($this->_conditions))) {
            list($condition, $value) = $elem;
            if ($request->getHeader($condition) != $value) {
                $dispatchable = false;
            }
        }

        return $dispatchable;
    }

    public function dispatch(\Zend_Controller_Request_Http $request)
    {
        $params = func_get_args();
        array_shift($params);

        if ($this->hasCallback('action')) {
            $this->call('action', $params);
        }

        $type = strtolower($request->getMethod());
        $this->call($type, $params);

        if ($request->isFlashRequest() && $this->hasCallback('flashRequest')) {
            $this->call('flashRequest', $params);
        }
        if ($request->isXmlHttpRequest() && $this->hasCallback('xmlHttpRequest')) {
            $this->call('xmlHttpRequest', $params);
        }
    }
}