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
    const THEME_FILENAME    = 'theme.phtml';
    const THEME_DIRNAME     = 'themes';
    const PAGES_DIRNAME     = 'pages';
    const RESPONSE_CODE     = '200';

    const LOGGING = true;

    public $basedir;
    public $baseurl;
    public $appdir;
    public $appurl;
    public $logfile;

    public $config_file;

    public $hooks = array();

    public $config;
    public $modules;

    public $themebasedir;
    public $themebaseurl;

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
        $this->baseurl  = 'http://'.$_SERVER['SERVER_NAME'].DS;  //I know, I'm lazy and should be fired...

        $this->appdir       = $this->cap($this->basedir.self::APPNAME);
        $this->appurl       = $this->baseurl.self::APPNAME.DS;

        $this->logfile  = $this->appdir.self::LOG_FILENAME;
        $this->modulesdir = $this->cap($this->appdir.self::MODULE_DIRNAME);

       // load config file...

        $this->config_file = $this->appdir.self::CONFIG_FILE;
        $this->config = $this->loadConfig(); // use the get() method to get values from this

        $this->set('paths/themedir', $this->cap($this->appdir.self::THEME_DIRNAME));
        $this->set('paths/themeurl', $this->appurl.self::THEME_DIRNAME.DS);

        $this->set('paths/pagesdir', $this->cap($this->appdir.self::PAGES_DIRNAME));
        $this->set('paths/pagesurl', $this->appurl.self::PAGES_DIRNAME.DS);

    }

    /*
     * Spins up the framework, does everything but render output
     *
     */
     public function init()
     {

         $this->loadModules(); //instantiate all the available modules
         $this->parseUrl(); // parse the url and invoke proper controller action

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

        if(empty($uri)) $this->get('homepage_uripath');
        $uri = explode('/', substr($_SERVER['REQUEST_URI'], 1));

        if($uri[0])
        {

            if(isset($this->modules->$uri[0]))
            {
                $module = $this->modules->$uri[0];

                if(isset($module['object']))
                {
                    if(!isset($uri[1])) $module['object']->indexAction(); else {

                        if(method_exists($module['object'], $module->$uri[1].'Action'))
                        {

                            call_user_func($module->$uri[1].'Action', $module['object']);

                        } else {

                            $this->set404();

                        }

                    }
                }
            } else {

                $this->set404();

            }

        } else {

            // setup homepage

            $home = $this->get('paths/pagesdir').'home.phtml';

            if(is_file($home)) $this->setHook('content', $home);

        }


    }

    public function set404()
    {
        $this->log('file not found: '.$_SERVER['REQUEST_URI']);

        $this->set('site/response_code', 404);

        $this->setHook('title', 'file not found');
        $this->setHook('content', $this->get('paths/pagesdir').'404.phtml');
    }

    /*
     * fetches a config option
     *
     * @param path as string to xpath from the config element i.e. database/host
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

            if(empty($_data)) return false; else return $_data;

        } catch(Exception $e) {

            return false;

        }

    }

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

        return;

    }

    public function setHook($hook, $val)
    {

        $this->hooks[$hook][] = $val;

    }

    public function getHook($hook = null, $delimiter = null)
    {

        if(isset($this->hooks[$hook]))
        {

            $html = false;

            foreach($this->hooks[$hook] as $content)
            {

                if(is_file($content))
                {

                    include($content);

                } else {

                    $html .= $delimiter . $content;

                }

            }

            return $html;

        } else {

            return false;
        }
    }


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

    /*
     *
     * Merges module hooks into this class' hooks for rendering
     *
     */

    public function hookMerge()
    {
//echo '<pre>';        var_dump($this->config); die();

        $_hooks = array_merge_recursive($this->hooks, $this->config['hooks']);

        foreach($this->modules as $module)
        {
               if(isset($module['object']->hooks)) $_hooks = array_merge_recursive($_hooks, $module['object']->hooks);
        }

        $this->hooks = $_hooks;

        return $this;

    }

    public function renderOutput($themename = null)
    {

        $this->hookMerge();

        if(!$themename) $themename = $this->get('themename');

        $theme = $this->cap($this->get('paths/themedir').$this->get('themename')).self::THEME_FILENAME;

        if(file_exists($theme))
        {

            $this->set('theme/path', $theme);
            $this->set('theme/url', $this->get('paths/themeurl').$themename.DS);

            if($this->get('site/response_code'))
                $response = $this->get('site/response_code');
            else $response = self::RESPONSE_CODE;

            header(':', true, $response);


            include($theme);


        } else {

            $this->setHook('error', 'Theme not found, expecting: '.$theme);
            var_dump($this->hooks);

        }

        if($this->get('site/debug') == 'true') $this->dumpDebug();

    }

    public function dumpDebug()
    {

        echo '<pre id = "debug"><ul>';

        echo '<li><h1>'.self::APPNAME.'</h1></li>';

        echo '<ul>';
            echo '<li><h2>Hooks</h2>';

            var_dump($this->hooks);

            echo '<li><h2>Config</h2>';

            var_dump($this->config);

            echo '<li><h2>Modules</h2>';

            var_dump($this->modules);
        echo '</ul>';
        echo '</ul></pre>';

    }


    public function getInstance()
    {
        return $this;
    }

}