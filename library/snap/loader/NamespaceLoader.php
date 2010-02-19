<?php

namespace snap\loader;

/**
 * Class loader based on SplClassLoader. Allows matching of subnamespaces which
 * may not necessarily map directly to directory names.
 *
 * Example:
 *     library
 *     `--foo
 *        |--bar
 *        |  `--Baz.php     (contains class foo\bar\Baz)
 *        |--baz_wat
 *        |  `--Bat
 *        |     `--Quux.php (contains class foo\random\Bat_Quux)
 *        `--Foo.php        (contains class foo\Foo)
 *
 * $fooLoader = new NamespaceLoader('foo', '/path/to/library/foo', array(
 *     'bar'    => 'bar',
 *     'random' => 'baz_wat',
 * ));
 * $fooLoader->register();
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman S. Borschel <roman@code-factory.org>
 * @author Matthew Weier O'Phinney <matthew@zend.com>
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 *
 * @author    Mon Zafra <monzee@gmail.com>
 * @copyright (c)2009-10 Jonathan Wage, Roman Borschel, Matthew Weier O'Phinney
 *            Kris Wallsmith, Fabien Potencier, Mon Zafra
 * @package   snap
 * @license   MIT License
 */
class NamespaceLoader
{
    protected $_fileExtension = '.php';
    protected $_namespace;
    protected $_includePath;
    protected $_namespaceSeparator = '\\';
    protected $_subNamespaces;

    /**
     * Creates a new class loader that loads classes of the specified namespace.
     *
     * @param string $ns    The namespace to match.
     * @param string $path  Where the classes live. Unlike SplClassLoader, this
     *                      _should_ include the directory corresponding to $ns.
     * @param array  $subNs Array of format <subnamespace> => <subpath>. More
     *                      specific subnamespaces should be defined first!
     *                      E.g. if you have entries for the subnamespaces foo and
     *                      foo\bar, foo\bar should be ahead of foo in the array
     */
    public function __construct($ns = null, $path = null, array $subNs = array())
    {
        $this->_namespace = $ns;
        $this->_includePath = $path;
        $this->_subNamespaces = $subNs;
    }

    /**
     * Sets the namespace separator used by classes in the namespace of this class loader.
     *
     * @param string $sep The separator to use.
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function setNamespaceSeparator($sep)
    {
        $this->_namespaceSeparator = $sep;
        return $this;
    }

    /**
     * Gets the namespace separator used by classes in the namespace of this class loader.
     *
     * @return void
     */
    public function getNamespaceSeparator()
    {
        return $this->_namespaceSeparator;
    }

    /**
     * Sets the base include path for all class files in the namespace of this class loader.
     *
     * @param string $includePath
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function setIncludePath($includePath)
    {
        $this->_includePath = $includePath;
        return $this;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $includePath
     */
    public function getIncludePath()
    {
        return $this->_includePath;
    }

    /**
     * Sets the file extension of class files in the namespace of this class loader.
     *
     * @param string $fileExtension
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function setFileExtension($fileExtension)
    {
        $this->_fileExtension = $fileExtension;
        return $this;
    }

    /**
     * Gets the file extension of class files in the namespace of this class loader.
     *
     * @return string $fileExtension
     */
    public function getFileExtension()
    {
        return $this->_fileExtension;
    }

    /**
     * Register a new subnamespace.
     * 
     * @param stirng $subnamespace
     * @param string $path
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function addSubnamespace($subnamespace, $path)
    {
        $this->_subNamespaces[$subnamespace] = $path;
        return $this;
    }

    /**
     * Replaces the registered subnamespaces.
     *
     * @param array $subns
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function setSubnamespaces(array $subns)
    {
        $this->_subNamespaces = $subns;
        return $this;
    }
    
    /**
     * Gets the registered subnamespaces of this loader.
     * 
     * @return array
     */
    public function getSubnamespaces()
    {
        return $this->_subNamespaces;
    }

    public function setNamespace($ns)
    {
        $this->_namespace = $ns;
        return $this;
    }

    /**
     * Installs this class loader on the SPL autoload stack.
     *
     * @param bool $prepend Whether to prepend or append to existing stack.
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
        return $this;
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     *
     * @return snap\loader\NamespaceLoader Fluent interface
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
        return $this;
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $className The name of the class to load.
     * @return void
     */
    public function loadClass($className)
    {
        $test = !empty($this->_namespace)
              ? $this->_namespace . $this->_namespaceSeparator
              : '';

        if (!$this->_stringStartsWith($className, $test)) {
            return;
        }

        foreach ($this->_subNamespaces as $ns => $subpath) {
            if ('' == $ns) {
                $subtest = $test;
            } else {
                $subtest = $test . $ns . $this->_namespaceSeparator;
                if (!$this->_stringStartsWith($className, $subtest)) {
                    continue;
                }
            }
            $path = $this->_includePath . DIRECTORY_SEPARATOR . $subpath;
            $parts = explode($this->_namespaceSeparator, substr($className, strlen($subtest)));
            $file = str_replace('_', DIRECTORY_SEPARATOR, array_pop($parts));
            $subnamespace = implode(DIRECTORY_SEPARATOR, $parts);
            $filename = !empty($subnamespace)
                      ? $path . DIRECTORY_SEPARATOR . $subnamespace . DIRECTORY_SEPARATOR . $file
                      : $path . DIRECTORY_SEPARATOR . $file;

            include_once $filename . $this->_fileExtension;
            return;
        }

        // no subnamespace matched and no blank subnamespace declared
        $lastSep = strrpos($className, $this->_namespaceSeparator);
        $subnamespace = substr($className, strlen($test), $lastSep - strlen($test) + 1);
        $class = substr($className, $lastSep + 1);
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class);
        $path = str_replace($this->_namespaceSeparator, DIRECTORY_SEPARATOR, $subnamespace);
        $basePath = !empty($this->_includePath) ? $this->_includePath . DIRECTORY_SEPARATOR : '';

        include_once $basePath . $path . $file . $this->_fileExtension;
    }

    /**
     * Tests if $haystack begins with $needle.
     *
     * @param string $haystack
     * @param string $needle
     * @param bool   $caseSensitive Compare as-is or strtolower both strings first?
     * @return bool
     */
    protected function _stringStartsWith($haystack, $needle, $caseSensitive = false)
    {
        if (!$caseSensitive) {
            $haystack = strtolower($haystack);
            $needle = strtolower($needle);
        }
        return empty($needle) || $needle === substr($haystack, 0, strlen($needle));
    }
}