<?php

/*
 *
 * This class will do all the low level work. We want to keep this modular, so the main class
 * will handle only the stuff applications will generally need, and everything else we'll
 * throw to a module/plugin. We'll use the URL parser to figure out which plugin is to
 * handle the request and then go about calling the plugin to do all the work. The plugin will
 * put all browser output in a 'hook' managed by the main class, and when the renderOutput method
 * is called, it will output a template using the hook contents to generate a fleshed out webpage.
 *
 */


class mycms {


    const APPNAME       = 'mycms';
    const CONFIG_FILE   = 'config.xml';
    const LOG_FILENAME  = 'log.txt';
    const MODULE_DIRNAME    = 'modules';

    const LOGGING = true;

    public $basedir;
    public $baseurl;
    public $appdir;
    public $logfile;

    public $config_file;

    public $hooks = array();

    public $config;
    public $modules;



    /*
     * Populate some variables and constants load config if needed etc
     */
    public function __construct()
    {

        define('DS', DIRECTORY_SEPARATOR);
        define('TAB', "\t");
        define('BR', PHP_EOL);

        // let's populate some basic variables we'll use all the time

        $this->basedir  = $this->cap($_SERVER['DOCUMENT_ROOT']);
        $this->appdir   = $this->cap($this->basedir.self::APPNAME);
        $this->baseurl  = 'http://'.$_SERVER['SERVER_NAME'].DS;  //I know, I'm lazy and should be fired...
        $this->logfile  = $this->appdir.self::LOG_FILENAME;
        $this->modulesdir = $this->cap($this->appdir.self::MODULE_DIRNAME);

       // load config file...

        $this->config_file = $this->appdir.self::CONFIG_FILE;
        $this->config = $this->loadConfig(); // use the get() method to get values from this





    }

    /*
     * Spins up the framework, does everything but render output
     *
     */
     public function init()
     {


         $this->loadModules(); //instantiate all the available modules
         $this->parseUrl(); // parse the url and invoke proper controller action



         var_dump($this->hooks, mycms::getHook());


     }


    public function loadModules()
    {

        foreach(scandir($this->get('modulesdir')) as $module)
        {

            if($module != "." || $module != "..")
            {
                $_thismodule = $this->cap($this->get('modulesdir').$module);

                $_controller = $_thismodule.'module.php';
                $_class = self::APPNAME.'_'.self::MODULE_DIRNAME.'_'.$module;

               if(is_file($_controller))
               {

                   require_once($this->get('modulesdir').'abstract.php');
                   require_once $_controller;

                    $this->modules->$module = array(

                        'path'          => $_thismodule,
                        'file'          => $_controller,
                        'classname'         => $_class,
                        'object'        => new $_class()

                    );
               }
            }

        }

    }


    public function parseUrl()
    {

        $uri = explode('/', substr($_SERVER['REQUEST_URI'], 1));

        var_dump($uri);

        $module = $this->modules->$uri[0];

        var_dump($module);

        if(isset($module['object']))
        {
            if(!isset($uri[1])) $module['object']->indexAction(); else call_user_func($module->$uri[1].'Action', $module['object']);
        }

    }


    /*
     * fetches a config option
     *
     * @param path as string to xpath of config item i.e. database/host
     */

    public function get($path)
    {

        $_path = explode('/', $path);

        $_data = false;

        try {

            foreach($_path as $part) if(!$_data) $_data = $this->config[$part]; else $_data = $_data[$part];
            return $_data;

        } catch(Exception $e) {

            return false;

        }

    }

    public function setHook($hook, $val)
    {

        $this->hooks[$hook][] = $val;

        var_dump($this->hooks);

    }

    public function getHook($hook = null)
    {
        if(!$hook) return $this->hooks; else return $this->hooks[$hook];
    }

//    public function set($path, $value)
//    {
//
//        $_path = explode('/', $path);
//
//        $_data = false;
//
//        try {
//
//            foreach($_path as $part) if(!$_data) $_data = '["'.$part.'"]'; else $_data .= '["'.$part.'"]';
//
//            var_dump($path, $this->get($path));
//
//
//            var_dump($_data, $this->config[$_data]);
//
//            $this->config[$_data] = $value;
//
//
//
//            var_dump($path, $this->get($path));
//
//            return $_data;
//
//
//
//        } catch(Exception $e) {
//
//            return false;
//
//        }
//
//
//
//
//    }

    public function loadConfig()
    {

        if(file_exists($this->config_file))
        {
            $_config = new SimpleXMLElement(file_get_contents($this->config_file));

            // json_decode(json_encode($object)) is a hack way to turn an object with nested data into a cleanish array
            $_config = array_merge(json_decode(json_encode($_config), true), json_decode(json_encode($this), true));

            // strip out a few things so the kids don't get confused in the trenches

            unset($_config['config']);
            unset($_config['hooks']);
            unset($_config['modules']);

            return $_config;

        } else {


            $this->log(self::CONFIG_FILE.' not found, exiting');
            die();

        }

    }

    public function log($message)
    {

        if(!self::LOGGING) return false;

        $callers = debug_backtrace();
        $_msg = BR.date("Y-m-d H:i:s").TAB.$message.TAB.$callers[1]['class'].'::'.$callers[1]['function'].' line: '.$callers[1]['line'];

        if(file_put_contents($this->logfile, $_msg, FILE_APPEND)) return true; else die($_msg);

    }


    /* prettifies paths and urls by ensuring
     * directories have trailing slashes
     *
     * @param string    dirty path
     * @return string   prettified path
     *
     */
    public function cap($path)
    {

        $_path = realpath($path);

        if(is_file($_path))
        {
            return $_path;

        } elseif(is_dir($_path)) {

            if(substr($_path, -1) != DS && is_dir($_path.DS))
            {
                return $_path.DS;

            } else {

                return $_path;
            }
        }

        return $path;

    }

}