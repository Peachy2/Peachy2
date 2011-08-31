<?php

define('PEACHY_VERSION', '2.0beta');

class pfCoreAutoload {

    static protected $registered = false;
    static protected $instance = null;
    protected $baseDir = null;

    protected function __construct() {
        $this->baseDir = realpath(dirname(__FILE__) . '/..');
    }

    static public function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new pfCoreAutoload();
        }

        return self::$instance;
    }

    static public function register() {
        if (self::$registered) {
            return;
        }

        ini_set('unserialize_callback_func', 'spl_autoload_call');
        if (false === spl_autoload_register(array(self::getInstance(), 'autoload'))) {
            throw new pfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
        }

        self::$registered = true;
    }

    static public function unregister() {
        spl_autoload_unregister(array(self::getInstance(), 'autoload'));
        self::$registered = false;
    }

    public function autoload($class) {
        $path = $this->getClassPath($class);
        if ($path) {
            require $path;

            return true;
        }

        return false;
    }

    public function getClassPath($class) {
        $class = strtolower($class);

        if (!isset($this->classes[$class])) {
            return null;
        }

        return $this->baseDir . '/' . $this->classes[$class];
    }

    public function getBaseDir() {
        return $this->baseDir;
    }
    
    static public function make() {
        $libDir = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
        require_once $libDir . '/vendor/symfony/lib/util/sfFinder.class.php';

        $files = sfFinder::type('file')
                ->prune('plugins')
                ->prune('misc')
                ->prune('helper')
                ->name('*.php')
                ->in($libDir)
        ;

        sort($files, SORT_STRING);

        $classes = '';
        foreach ($files as $file) {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);
            $class = basename($file, false === strpos($file, '.class.php') ? '.php' : '.class.php');

            $contents = file_get_contents($file);
            if (false !== stripos($contents, 'class ' . $class) || false !== stripos($contents, 'interface ' . $class)) {
                $classes .= sprintf("    '%s' => '%s',\n", strtolower($class), substr(str_replace($libDir, '', $file), 1));
            }
        }

        $content = preg_replace('/protected \$classes = array *\(.*?\);/s', sprintf("protected \$classes = array(\n%s  );", $classes), file_get_contents(__FILE__));

        file_put_contents(__FILE__, $content);
    }

    // Don't edit this property by hand.
    // To update it, use sfCoreAutoload::make()
    protected $classes = array(
    'pfcoreautoload' => 'autoload/pfCoreAutoload.class.php',
    'pfbaseconfiguration' => 'config/pfBaseConfiguration.class.php',
    'sfevent' => 'vendor/sfEventDispatcher/lib/sfEvent.php',
    'sfeventdispatcher' => 'vendor/sfEventDispatcher/lib/sfEventDispatcher.php',
    'sfyaml' => 'vendor/sfYaml/lib/sfYaml.php',
    'sfyamldumper' => 'vendor/sfYaml/lib/sfYamlDumper.php',
    'sfyamlinline' => 'vendor/sfYaml/lib/sfYamlInline.php',
    'sfyamlparser' => 'vendor/sfYaml/lib/sfYamlParser.php',
    'sffinder' => 'vendor/symfony/lib/util/sfFinder.class.php',
    'pffinder' => 'vendor/symfony/pfFinder.class.php',
  );

}
