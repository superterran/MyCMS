<?php
/**
 * Currently, the http file contains a class that extends off this to provide working browser support.
 * I intend to build a cli class that extends off this to provide working cli/terminal support.
 *
 * @package MyCMS
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */
/**
 * This is the basic framework. Loading this will give you modules, config and hooks as well as
 * logging and all the helper fuctions needed to do some basic stuff.  *
 */
abstract class mycms
{
    const APPNAME   = 'mycms';
    public $appdir;
    public $appurl;

    public $basedir;
    public $baseurl;

    const LOG_FILENAME  = 'log.txt';
    const RESPONSE_CODE = '200';

    const LOGGING           = true;
    public $logfile;

    const CONFIG_FILE       = 'config.xml';
    public $config_file;
    public $config;

    const MODULE_DIRNAME    = 'modules';
    public $modules;
    public $hooks = array();

    /**
     * populates some things to complete framework instantiation. We want to separate out
     * framework instantiation and the next steps of the application workflow, so this
     * defines some constants, sets up a few variables and instantiates the framework
     * modules.
     */
    public function __construct()
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('TAB', "\t");
        define('BR', PHP_EOL);

        date_default_timezone_set('UTC'); // Shuts up apache on Arch Linux

        // let's populate some basic variables we'll use all the time

        $this->basedir  = $this->cap($_SERVER['DOCUMENT_ROOT']);
        $this->baseurl  = 'http://'.$_SERVER['SERVER_NAME'].DS;  //I know, I'm lazy and should be fired...

        $this->appdir       = $this->cap($this->basedir.self::APPNAME);
        $this->appurl       = $this->baseurl.self::APPNAME.DS;

        $this->logfile  = $this->appdir.self::LOG_FILENAME;

        $this->config_file = $this->appdir.self::CONFIG_FILE;
        $this->config = $this->loadConfig(); // use the get() method to get values from this

        $this->set('paths/modulesdir', $this->cap($this->appdir.self::MODULE_DIRNAME));
        $this->set('paths/modulesurl', $this->appurl.self::MODULE_DIRNAME.DS);

        //instantiate all the available modules
        $this->loadModules();

    }

   /**
    * fetches a config option from $this->config. get() provides a common method
    * to fetch this kind of thing, Use with it's brother set();
    *
    * This abstraction is useful since we will want to
    * load/save stuff in a db at some point, we can use these getters and setters
    * so the rest of the application can automatically take advantage of the
    * new features when the time comes.
    *
    * @param string $path xpath from the config element i.e. database/host
    * @return false|string $value from config
    */
    public function get($path)
    {

        $_path = explode('/', $path);

        $_data = false;

        try {

            foreach($_path as $part)
            {
                if(!$_data)
                {
                    if(isset($this->config[$part])) $_data = $this->config[$part];

                } else {

                    if(isset($_data[$part])) $_data = $_data[$part]; else return false;
                }
            }

            if(!empty($_data)) {

                /**
                 * @todo clean up returns
                 * ex. empty values tend to be returned as empty arrays. See database/password
                 */

                return $_data;

            }

        } catch(Exception $e) {

            return false;

        }

        return false;

    }


    /**
     * This sets a configuration option to a given value. These do not persist
     * after page has finised rendering.
     *
     * @todo implement without using json_decode()
     * @param $path xpath to config option
     * @param $value value to set config option to
     * @return $this;
     */
    public function set($path, $value)
    {

        $uri = explode('/', $path);

        $front  = false;
        $back   = false;

        foreach($uri as $part)
        {

            $front .= '{ "'.$part.'" : ';
            $back  = " }".$back;

        }

        $new = json_decode($front.'"'.$value.'"'.$back, 1);

        $this->config = array_merge_recursive($this->config, $new);

        return $this;

    }

    /**
     * Loads the framework config. Does this by loading config.xml and merging
     * in an arrayed version of $this object. Later will also merge in config from database
     *
     * @return array $_config array of entire configuration
     */
    public function loadConfig()
    {

        if(file_exists($this->config_file))
        {
            $_config = new SimpleXMLElement(file_get_contents($this->config_file));

            // json_decode(json_encode($object)) is a hack way to turn an object with nested data into a cleanish array
            $_config = array_merge_recursive(json_decode(json_encode($_config), true), json_decode(json_encode($this), true));

            // strip out a few things so the kids don't get confused in the trenches

            // unset($_config['config']);
            unset($_config['modules']);

            return $_config;

        } else {


            $this->log(self::CONFIG_FILE.' not found, exiting');
            die();

        }

    }

    /**
     * Loads modules into memory, assigns them to an $this->modules array.
     *
     * $modules['modulename'] = array(
     *      'path'      => 'path/to/this/modulename/',
     *      'file'      => 'module.php',
     *      'classname' => 'mycms_modules_modulename',
     *      'object'    => instance of mycms_modules_modulename
     * );
     */
    public function loadModules()
    {

        // instantiate modules

        foreach(scandir($this->get('paths/modulesdir')) as $module)
        {

            if($module != "." || $module != "..")
            {
                $_thismodule = $this->cap($this->get('paths/modulesdir').$module);

                $_controller = $_thismodule.'module.php';
                $_class = self::APPNAME.'_'.self::MODULE_DIRNAME.'_'.$module;

                if(is_file($_controller))
                {

                    require_once($this->get('paths/modulesdir').'interface.php');
                    require_once($this->get('paths/modulesdir').'abstract.php');

                    require_once $_controller;

                    $this->modules[$module] = array(

                        'path'          => $_thismodule,
                        'file'          => $_controller,
                        'classname'         => $_class,
                        'object'        => new $_class($this)

                    );
                }
            }

        }

    }

    /**
     * Calls modules actions and passes parameters. This allows the framework to
     * access module features.
     *
     * @param string $module as module name
     * @param string $method as action method
     * @param null|array $params as additional parameters
     * @return mixed
     */
    public function go($module, $method, $params = null)
    {
        try{

            return $this->modules[$module]['object']->$method($params);

        } catch(Exception $e) {

            $this->log('Error: Could not call controller. '.$module. ', '.$method);

        }

    }


    /**
     * Sets a value to a hook. This method allows you to populate hooks to render template
     * at the end of the framework workflow.
     *
     * @param string $hook name of hook e.g. 'content'
     * @param string $val value of hook (either html or an absolute path to a .phtml file)
     */
    public function setHook($hook, $val)
    {

        $this->hooks[$hook][] = $val;

    }

    /**
     * Fetches data from hook and prepares them for rendering/output. This is one of the
     * goto template methods to actually build out the webpage.
     *
     * @param null|string $hook name of hook
     * @param null|string $delimiter string to use as a delimiter (i.e. for title hook)
     * @return bool|string | string html to echo in template.
     */
    public function getHook($hook = null, $delimiter = null)
    {

        if(isset($this->hooks[$hook]))
        {
            $html = false;

            foreach($this->hooks[$hook] as $content)
            {
                if(is_file($content)) include($content); else $html .= $delimiter . $content;
            }

            return $html;

        } else {

            return false;
        }
    }

    /**
     * Writes or outputs contents of $message to a log file or to screen
     *
     * @param string $message
     * @return bool
     */
    public function log($message)
    {

        if(!self::LOGGING) return false;

        $callers = debug_backtrace();
        $_msg = BR.date("Y-m-d H:i:s").TAB.$message.TAB.$callers[1]['class'].'::'.$callers[1]['function'].' line: '.$callers[1]['line'];

        if(file_put_contents($this->logfile, $_msg, FILE_APPEND)) return true; else die($_msg);

    }

    /**
     * This is a helper function that prettifies paths by ensuring directories have trailing slashes
     *
     * @param string $path dirty path
     * @return string $path prettified path
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

    /**
     * generates some basic debug output
     */
    public function dumpDebug()
    {

        var_dump($this->hooks, $this->config, $this->modules);

    }


}