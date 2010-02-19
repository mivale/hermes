<?php

namespace snap;

/**
 * Front controller
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2009-10 Mon Zafra
 * @package    snap
 * @license    MIT License
 */
class Front extends \Zend_Controller_Front
{
    protected $_contexts = array();
    protected $_contextKey = 'context';
    protected $_contextName = 'default';
    protected $_routes = array();
    protected $_container;
    protected $_aliasSpec = 'snap_:num';
    protected $_invokeParams = array(
        'viewPath' => '../views',
        'layout' => 'layout',
    );

    static public function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    public function setConfig($config, $section = null)
    {
        if (is_string($config)) {
            $config = $this->_loadConfigFile($config, $section);
        }

        if ($config instanceof \Zend_Config) {
            $config = $config->toArray();
        } else {
            $config = (array) $config;
        }

        $this->_invokeParams = array_merge($this->_invokeParams, $config);
        return $this;
    }

    protected function _loadConfigFile($config, $section)
    {
        if (!file_exists($config)) {
            throw new Exception('Configuration file does not exist.');
        }
        $ext = pathinfo($config, PATHINFO_EXTENSION);
        switch (strtolower($ext)) {
            case 'php':
                return include $config;
            case 'ini':
                return new \Zend_Config_Ini($config, $section);
            case 'xml':
                return new \Zend_Config_Xml($config, $section);
            default:
                throw new Exception('Invalid configuration file.');
                break;
        }
    }

    public function __get($property)
    {
        $property = strtolower($property);
        return $this->getContainer()->$property;
    }

    public function getContainer()
    {
        if (null === $this->_container) {
            $this->_container = new Container(array(
                'front' => $this,
                'invoke' => $this->getParams(),
                'request' => $this->getRequest(),
                'response' => $this->getResponse(),
            ));
        }
        return $this->_container;
    }

    public function __invoke($pattern = '*', $alias = null)
    {
        return $this->route($pattern, $alias);
    }

    public function route($pattern = '*', $alias = null)
    {
        if (!isset($this->_routes[$pattern])) {
            $alias = $alias ?: $this->_generateAlias();
            $this->addContext($pattern, 'route', $alias);
        } else {
            $alias = $this->_routes[$pattern];
        }

        return $this->_contexts[$alias];
    }

    public function regex($pattern, $alias = null)
    {
        if (!isset($this->_routes[$pattern])) {
            $alias = $alias ?: $this->_generateAlias();
            $this->addContext($pattern, 'regex', $alias);
        } else {
            $alias = $this->_routes[$pattern];
        }

        return $this->_context[$alias];
    }

    public function addContext($pattern, $type, $alias)
    {
        $type = 'snap\\router\\' . ucfirst($type);
        $route = new $type($pattern, array($this->_contextKey => $alias));
        $context = new Context($route, $this->_contextKey);
        $this->getRouter()->addRoute($alias, $route);
        $this->_contexts[$alias] = $context;
        $this->_routes[$pattern] = $alias;
        return $this;
    }

    protected function _generateAlias()
    {
        $routes = (array) $this->_routes; // the casting is needed by phpunit. this becomes null for some reason.
        $names = array_values($routes);
        $i = 1;
        do {
            $ret = $this->getAliasBySpec($i++);
        } while (in_array($ret, $names));
        return $ret;
    }

    public function getAliasBySpec($number)
    {
        $spec = $this->_aliasSpec;
        if (false === strpos($spec, ':num')) {
            return $spec . $number;
        }
        return str_replace(':num', $number, $spec);
    }

    public function getRequest()
    {
        if (null === $this->_request) {
            $this->_request = new \Zend_Controller_Request_Http();
        }
        return $this->_request;
    }

    public function getResponse()
    {
        if (null === $this->_response) {
            $this->_response = new \Zend_Controller_Response_Http();
        }
        return $this->_response;
    }

    public function dispatch(\Zend_Controller_Request_Abstract $request = null, \Zend_Controller_Response_Abstract $response = null)
    {
        (null !== $request and $this->setRequest($request))
            or $request = $this->getRequest();

        null === $this->_baseUrl or
            (method_exists($request, 'setBaseUrl') and $request->setBaseUrl($this->_baseUrl));

        if (null !== $response) {
            $this->setResponse($response);
        } else {
            $response = $this->getResponse();
        }

        $this->_plugins->setRequest($request)->setResponse($response);

        $router = $this->getRouter();
        $router->setParams($this->_invokeParams);


        try {
            $this->_plugins->routeStartup($request);

            $router->route($request);
            $this->_plugins->routeShutdown($request);
            $this->_plugins->dispatchLoopStartup($request);

            // if a snap route is matched, use snap helpers and dispatcher
            // TODO: must dispatch like a normal ZF app otherwise
            if (null !== $this->_contextName) {
                \Zend_Controller_Action_HelperBroker::addPath('snap/helper', 'snap\helper');
                if (!$this->_dispatcher instanceof Dispatcher) {
                    $this->setDispatcher(new Dispatcher($this));
                }
            }

            $dispatcher = $this->getDispatcher();
            $dispatcher->setParams($this->_invokeParams);

            do {
                // this will loop only if this flag was changed at some point
                $request->setDispatched(true);

                $this->_plugins->preDispatch($request);
                // repeat preDispatch if a plugin reset the dispatched flag
                if (!$request->isDispatched()) {
                    continue;
                }

                try {
                    // because the postDispatch still needs to happen even if the
                    // action fails if throwExceptions == false
                    $dispatcher->dispatch($request, $response);
                } catch (Exception $e) {
                    if ($this->throwExceptions()) {
                        throw $e;
                    }
                    $response->setException($e);
                }

                $this->_plugins->postDispatch($request);
            } while (!$request->isDispatched());

            $this->_plugins->dispatchLoopShutdown();
        } catch (Exception $e) {
            if ($this->throwExceptions()) {
                throw $e;
            }
            $response->setException($e);
        }

        if ($this->returnResponse()) {
            return $response;
        }

        $response->sendResponse();
    }

    public function setContainer($container)
    {
        if (null === $container->invoke) {
            $container->invoke = $this->getParams();
        }
        if (null === $container->request) {
            $container->request = $this->getRequest();
        }
        if (null === $container->response) {
            $container->response = $this->getResponse();
        }

        $this->_container = $container;
        return $this;
    }

    public function getContext($alias)
    {
        return isset($this->_contexts[$alias]) ? $this->_contexts[$alias] : null;
    }

    public function getContextName()
    {
        return $this->_contextName;
    }

    public function setContextName($name)
    {
        $this->_contextName = $name;
        $this->getRequest()->setDispatched(false);
        return $this;
    }

    public function setContextKey($key)
    {
        $this->_contextKey = $key;
        return $this;
    }

    public function getContextKey()
    {
        return $this->_contextKey;
    }

    public function setAliasSpec($spec)
    {
        $this->_aliasSpec = $spec;
        return $this;
    }

    // not sure yet if these should be here.

    protected $_controllerCallbacks = array();

    public function addCallback($type, $callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Invalid callback specified.');
        }
        $type = strtolower($type);
        if (!isset($this->_controllerCallbacks[$type])) {
            $this->_controllerCallbacks[$type] = array();
        }
        $this->_controllerCallbacks[$type][] = $callback;
        return $this;
    }

    public function getCallbacks()
    {
        return $this->_controllerCallbacks;
    }

    public function getCallback($type)
    {
        $type = strtolower($type);
        return $this->_controllerCallbacks[$type];
    }

    public function init($callback)
    {
        return $this->addCallback('init', $callback);
    }

    public function preDispatch($callback)
    {
        return $this->addCallback('preDispatch', $callback);
    }

    public function postDispatch($callback)
    {
        return $this->addCallback('postDispatch', $callback);
    }


}
