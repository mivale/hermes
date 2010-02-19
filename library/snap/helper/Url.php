<?php

namespace snap;

class helper_Url extends \Zend_Controller_Action_Helper_Abstract
{
    public function direct($route = null, $params = array(), $reset = false, $escape = true)
    {
        $front = $this->getFrontController();
        $router = $front->getRouter();
        if (is_int($route)) {
            $route = $front->getAliasBySpec($route);
        }
        return $router->assemble($params, $route, $reset, $escape);
    }
}
