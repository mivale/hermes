<?php

namespace snap;

/**
 * Controller
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2009-10 Mon Zafra
 * @package    snap
 * @license    MIT License
 */
class Controller extends \Zend_Controller_Action
{
    protected $_callbacks = array();

    public function setCallbacks(array $callbacks)
    {
        $this->_callbacks = $callbacks;
        return $this;
    }

    public function setCallback($type, $callback)
    {
        $type = strtolower($type);
        if (!is_callable($callback) || !in_array($type, array('init', 'predispatch', 'postdispatch'))) {
            throw new Exception('Invalid callback or event type specified.');
        }
        $this->_callbacks[$type] = $callback;
        return $this;
    }

    public function init()
    {
        $this->_callbacks = $this->getFrontController()->getCallbacks();
        if (isset($this->_callbacks['init'])) {
            $container = $this->getFrontController()->getContainer();
            $callbacks = $this->_callbacks['init'];
            foreach ($callbacks as $callback) {
                call_user_func($callback, $container, $this);
            }
        }
    }

    public function preDispatch()
    {
        if (isset($this->_callbacks['predispatch'])) {
            $container = $this->getFrontController()->getContainer();
            $callbacks = $this->_callbacks['predispatch'];
            foreach ($callbacks as $callback) {
                call_user_func($callback, $container, $this);
            }
        }
    }

    public function postDispatch()
    {
        if (isset($this->_callbacks['postdispatch'])) {
            $container = $this->getFrontController()->getContainer();
            $callbacks = $this->_callbacks['postdispatch'];
            foreach ($callbacks as $callback) {
                call_user_func($callback, $container, $this);
            }
        }
    }

    public function initView()
    {
        if (!isset($this->view)) {
            $this->_helper->viewRenderer->initView();
        }
        return $this->view;
    }

    public function dispatch($action)
    {
        $front = $this->getFrontController();
        $context = $front->getContext($action);
        $container = $front->getContainer();

        if ($context->hasView()) {
            $this->_helper->viewRenderer
                          ->setViewScriptBySpec($context->getViewScript());
        };

        $container->view = $this->initView();
        $container->helper = $this->_helper;

        $this->_helper->notifyPreDispatch();

        $this->preDispatch();
        if ($this->_request->isDispatched()) {
            $context->dispatch($this->_request, $container);
            $this->postDispatch();
        }

        $this->_helper->notifyPostDispatch();
    }
}