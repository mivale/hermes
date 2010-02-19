<?php

namespace snap\router;

use snap\Front;

class Route extends \Zend_Controller_Router_Route
{
    public function getPattern()
    {
        return $this->_route;
    }

    public function setDefault($key, $val)
    {
        $this->_defaults[$key] = $val;
        return $this;
    }

    public function setRequirement($key, $val)
    {
        $pos = array_search($key, $this->_variables);
        if (false !== $pos) {
            $this->_parts[$pos] = $val;
        }
        return $this;
    }

    public function match($path, $partial = false)
    {
        $result = parent::match($path, $partial);
        if (false === $result) {
            return false;
        }

        $front = Front::getInstance();
        $contextKey = $front->getContextKey();

        $context = $front->getContext($result[$contextKey]);
        if (!$context->isDispatchable($front->getRequest())) {
            return false;
        }

        $front->setContextName($result[$contextKey]);
        return $result;
    }
}
