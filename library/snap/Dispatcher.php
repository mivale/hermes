<?php

namespace snap;

class Dispatcher extends \Zend_Controller_Dispatcher_Abstract
{
    protected $_controller;

    public function __construct(Front $front)
    {
        $this->_frontController = $front;
    }

    public function getActionController($request = null)
    {
        if (null === $this->_controller) {
            $this->_controller = new Controller(
                $request,
                $this->_response,
                $this->getParams()
            );
            $this->_controller
                 ->setFrontController($this->_frontController);
        }
        return $this->_controller;
    }

    public function isDispatchable(\Zend_Controller_Request_Abstract $request)
    {
        $context = $this->_frontController->getContext($this->getContextName());
        if (null === $context) {
            return false;
        }

        return $context->isDispatchable();
    }

    public function dispatch(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        $context = $this->_frontController->getContextName();
        $controller = $this->getActionController($request);

        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');
        $obLevel   = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            $controller->dispatch($context);
        } catch (Exception $e) {
            // Clean output buffer on error
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }

            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        $context = null;
    }

    // unused interface methods

    /**
     * Formats a string into a controller name.  This is used to take a raw
     * controller name, such as one that would be packaged inside a request
     * object, and reformat it to a proper class name that a class extending
     * Zend_Controller_Action would use.
     *
     * @param string $unformatted
     * @return string
     */
    public function formatControllerName($unformatted)
    {
        return $unformatted;
    }

    /**
     * Formats a string into a module name.  This is used to take a raw
     * module name, such as one that would be packaged inside a request
     * object, and reformat it to a proper directory/class name that a class extending
     * Zend_Controller_Action would use.
     *
     * @param string $unformatted
     * @return string
     */
    public function formatModuleName($unformatted)
    {
        return $unformatted;
    }

    /**
     * Formats a string into an action name.  This is used to take a raw
     * action name, such as one that would be packaged inside a request
     * object, and reformat into a proper method name that would be found
     * inside a class extending Zend_Controller_Action.
     *
     * @param string $unformatted
     * @return string
     */
    public function formatActionName($unformatted)
    {
        return $unformatted;
    }

    /**
     * Add a controller directory to the controller directory stack
     *
     * @param string $path
     * @param string $args
     * @return Zend_Controller_Dispatcher_Interface
     */
    public function addControllerDirectory($path, $args = null)
    {
        return $this;
    }

    /**
     * Set the directory where controller files are stored
     *
     * Specify a string or an array; if an array is specified, all paths will be
     * added.
     *
     * @param string|array $dir
     * @return Zend_Controller_Dispatcher_Interface
     */
    public function setControllerDirectory($path)
    {
        return $this;
    }

    /**
     * Return the currently set directory(ies) for controller file lookup
     *
     * @return array
     */
    public function getControllerDirectory()
    {
        return array();
    }

    /**
     * Whether or not a given module is valid
     *
     * @param string $module
     * @return boolean
     */
    public function isValidModule($module)
    {
        return false;
    }

}
