<?php

namespace snap;

use snap\Config;
// TODO: change namespace to snap\helper when the helperb0rker gets fixed.

class helper_ViewRenderer extends \Zend_Controller_Action_Helper_Abstract
{
    public $view;
    protected $_scriptpath = './views';
    protected $_helperpath;
    protected $_filterpath;
    protected $_viewscript;
    protected $_scriptsuffix = 'phtml';
    protected $_norender = false;
    protected $_neverrender = false;
    protected $_responsesegment = null;
    protected $_prefix = 'Zend_View';

    public function setView(\Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    public function getViewScriptBySpec($scriptSpec)
    {
        if (preg_match_all('/:(\w+)/', $scriptSpec, $matches)) {
            $keys = $matches[1];
            $replace = array();
            foreach ($keys as &$key) {
                $replace[] = (string) $container->$key;
                $key = ':' . $key;
            }
            $scriptSpec = str_replace($keys, $replace, $scriptSpec);
        }
        return $scriptSpec;
    }

    public function setViewScriptBySpec($scriptSpec)
    {
        $this->_viewscript = $this->getViewScriptBySpec($scriptSpec);
        return $this;
    }

    public function getScriptFile($script = null)
    {
        $suffix = '.' . $this->_scriptsuffix;
        $script = $script ?: $this->_viewscript;
        if ($suffix == substr($script, -strlen($suffix))) {
            return $script;
        }
        return $script . $suffix;
    }

    public function setRender($script, $name = null)
    {
        $this->_viewscript = $script;
        $this->_responsesegment = $name;
        return $this;
    }

    public function setNoRender($flag = true)
    {
        $this->_norender = $flag;
        return $this;
    }

    public function setNeverRender($flag = true)
    {
        $this->_neverrender = $flag;
        return $this;
    }

    public function __call($method, $args)
    {
        $method = strtolower($method);
        $first3 = substr($method, 0, 3);
        if ('set' == $first3) {
            $prop = '_' . substr($method, 3);
            if (property_exists($this, $prop)) {
                $this->$prop = $args[0];
                return $this;
            }
        } else if ('get' == $first3) {
            $prop = '_' . substr($method, 3);
            if (property_exists($this, $prop)) {
                return $this->$prop;
            }
        }
        throw new \BadMethodCallException('No such method (' . $method . ').');
    }

    public function init()
    {
        $stack = \Zend_Controller_Action_HelperBroker::getStack();
        if (isset($stack['ViewRenderer'])) {
            unset($stack['ViewRenderer']);
        }
        $stack[-80] = $this;
        $front = $this->getFrontController();
        $this->_scriptpath = $front->getParam('viewPath') ?: $this->_scriptPath;
        $this->initView();
    }

    public function initView($path = null, $prefix = null)
    {
        if (null === $this->view) {
            $this->setView(new \Zend_View());
        }
        $view = $this->view;

        $path = $path ?: $this->_scriptpath;
        $helperPath = $this->_helperpath ?: $path . '/helpers';
        $filterPath = $this->_filterpath ?: $path . '/filters';
        $prefix = $prefix ?: $this->_prefix;
        $prefix = rtrim($prefix, '_') . '_';

        $view->addScriptPath($path)
            ->addHelperPath($helperPath, $prefix . 'Helper')
            ->addFilterPath($filterPath, $prefix . 'Filter');

        if (null !== ($controller = $this->getActionController())) {
            $controller->view = $view;
        }
        return $this;
    }

    public function render($scriptSpec = null, $name = null)
    {
        $spec = $scriptSpec ?: $this->_viewscript;
        $script = $this->getViewScriptBySpec($spec);
        $file = $this->getScriptFile($script);
        $this->renderScript($file, $name);
    }

    public function renderScript($path, $name = null)
    {
        $segment = $name ?: $this->_responsesegment;
        $this->getResponse()
            ->appendBody(
                $this->view->render($path),
                $segment
            );
        $this->_norender = true; // render only once
    }

    /**
     * postDispatch - auto render a view
     *
     * Only autorenders if:
     * - _noRender is false
     * - action controller is present
     * - viewscript is set
     * - request has not been re-dispatched (i.e., _forward() has not been called)
     * - response is not a redirect
     *
     * @return void
     */
    public function postDispatch()
    {
        if ($this->_shouldRender()) {
            $this->render();
        }
    }

    /**
     * Should the ViewRenderer render a view script?
     *
     * @return boolean
     */
    protected function _shouldRender()
    {
        return ((null !== $this->_viewscript)
//            && !$this->getFrontController()->getParam('noViewRenderer')
            && !$this->_neverrender
            && !$this->_norender
            && (null !== $this->_actionController)
            && $this->getRequest()->isDispatched()
            && !$this->getResponse()->isRedirect()
        );
    }

}