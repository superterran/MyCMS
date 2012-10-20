<?php

require_once('abstract.php');

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


class mycms extends framework_abstract {


    const APPNAME       = 'mycms';

    const MODULE_DIRNAME    = 'modules';
    const THEME_FILENAME    = 'theme.phtml';
    const THEME_DIRNAME     = 'themes';
    const PAGES_DIRNAME     = 'pages';
    const RESPONSE_CODE     = '200';

    public $hooks = array();

    public $modules;

    public $themebasedir;
    public $themebaseurl;


    /*
     * Populate some variables and constants load config if needed etc
     */
    public function __construct()
    {





        $this->modulesdir = $this->cap($this->appdir.self::MODULE_DIRNAME);

       // load config file...



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

        // instantiate modules

        foreach(scandir($this->get('modulesdir')) as $module)
        {

            if($module != "." || $module != "..")
            {
                $_thismodule = $this->cap($this->get('modulesdir').$module);

                $_controller = $_thismodule.'module.php';
                $_class = self::APPNAME.'_'.self::MODULE_DIRNAME.'_'.$module;

               if(is_file($_controller))
               {

                   require_once($this->get('modulesdir').'interface.php');
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