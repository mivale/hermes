<?php

namespace snap\loader;

/**
 * Finds a class from a list of namespaces, emitting warnings for every failure.
 * Utilizes the autoloader.
 *
 * @author     Mon Zafra <monzee at gmail>
 * @copyright  (c)2010 Mon Zafra
 * @package    snap
 * @subpackage loader
 * @license    MIT License
 */
class FactoryLoader
{
    protected $_prefixes;
    protected $_separator = '\\';
    
    public function __construct($prefixes = array(), $separator = '\\')
    {
        $this->_prefixes = $prefixes;
        $this->_separator = $separator;
    }
    
    public function pushPrefix($prefix)
    {
        $this->_prefixes[] = $prefix;
        return $this;
    }

    public function unshiftPrefix($prefix)
    {
        array_unshift($this->_prefixes, $prefix);
        return $this;
    }
    
    public function unsetPrefix($prefix)
    {
        if (array_key_exists($prefix, $this->_prefixes)) {
            unset($this->_prefixes[$prefix]);
        }
        return $this;
    }

    public function setPrefixes(array $prefixes)
    {
        $this->_prefixes = $prefixes;
        return $this;
    }

    public function getPrefixes()
    {
        return $this->_prefixes;
    }

    public function getSeparator()
    {
        return $this->_separator;
    }
    
    public function setSeparator($separator)
    {
        $this->_seprator = $separator;
        return $this;
    }

    /**
     * Invokes the autoloader for each prefix, warnings be damned.
     *
     * @param string $type
     * @return string|bool Full name of the first class that has this strategy,
     *                     false if no match was found.
     */
    public function load($type)
    {
        $match = false;
        $backup = error_reporting();
        error_reporting($backup & ~E_WARNING);
        foreach ($this->_prefixes as $prefix) {
            $test = $prefix . $this->_separator . $type;
            if (class_exists($test, true)) {
                $match = $test;
                break;
            }
        }
        error_reporting($backup);
        return $match;
    }

}