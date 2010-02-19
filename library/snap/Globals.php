<?php

namespace snap {

/**
 * Dummy class to trigger the autoloader and load the global functions below.
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2010 Mon Zafra
 * @package    snap
 * @license    MIT License
 */
abstract class Globals
{
    static public function register()
    {
        // pass;
    }
}

}

namespace {
    use snap\Snap;

    function front()
    {
        return Snap::Application()->front;
    }

    function setup()
    {
        $args = func_get_args();
        return call_user_func_array(array('snap\Snap', 'setup'), $args);
    }

    function action()
    {
        $args = func_get_args();
        return Snap::__callStatic('action', $args);
    }

    function get()
    {
        $args = func_get_args();
        return Snap::__callStatic('get', $args);
    }

    function post()
    {
        $args = func_get_args();
        return Snap::__callStatic('post', $args);
    }

    function put()
    {
        $args = func_get_args();
        return Snap::__callStatic('put', $args);
    }

    function delete()
    {
        $args = func_get_args();
        return Snap::__callStatic('delete', $args);
    }

    function head()
    {
        $args = func_get_args();
        return Snap::__callStatic('head', $args);
    }

    function options()
    {
        $args = func_get_args();
        return Snap::__callStatic('options', $args);
    }

    function ajax()
    {
        $args = func_get_args();
        return Snap::__callStatic('ajax', $args);
    }

    function flash()
    {
        $args = func_get_args();
        return Snap::__callStatic('flash', $args);
    }

    function run()
    {
        $args = func_get_args();
        return call_user_func_array(array('snap\Snap', 'run'), $args);
    }

    function dispatch()
    {
        return run();
    }
}