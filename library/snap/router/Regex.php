<?php

namespace snap\router;

use snap\Front;

class Regex extends \Zend_Controller_Router_Route_Regex
{
    public function map($key, $value)
    {
        $this->_map[$key] = $value;
        return $this;
    }

    public function setMap($map)
    {
        $this->_map = $map;
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