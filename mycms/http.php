<?php
/**
 * This is required from the entry point. This extends the basic framework and adds templates,
 * pages and all else to the mix to build out the basic web application.
 *
 * @package MyCMS
 * @url https://github.com/superterran/MyCMS
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */

/**
 * This is extended from the general framework which handles the low level stuff.
 * @see mycms_abstract
 */
require_once('framework.php');

/**
 * This class performs the http specific work to render webpages whereas the abstract has all the logic
 * for a general implementation. This class will handle loading and rendering themes and browser output and
 * is called from the entry point.
 */
class mycms_http extends mycms {


    const THEME_FILENAME    = 'theme.phtml';
    const THEME_DIRNAME     = 'themes';

    const PAGES_DIRNAME     = 'pages';

    const LOGGING = true;


    /**
     * Loads the parent constructor (paths and modules), then sets required paths for pages and themes.
     */
    public function __construct()
    {
        parent::__construct();

        $this->set('paths/themedir', $this->cap($this->appdir.self::THEME_DIRNAME));
        $this->set('paths/themeurl', $this->appurl.self::THEME_DIRNAME.DS);

        $this->set('paths/pagesdir', $this->cap($this->appdir.self::PAGES_DIRNAME));
        $this->set('paths/pagesurl', $this->appurl.self::PAGES_DIRNAME.DS);

    }

    /**
     * Initiates URL Parser, this will call the controller system, as well as any other
     * logic we need handled at this point. This is called by the entry point to denote
     * that it is ready to handle the request.
     */
    public function init()
     {
         $this->parseUrl();
     }


    /**
     * The URL Parser takes the Request URI and works out what module and controller
     * action to file in order to complete the page request. This calls the go() function defined
     * in the abstract, which executes the desired action. Any extra url data is pushed to the
     * controller action as a param array. Check {@link mycms_modules_example::urlparamAction()} for details.
     * It'll also set these parameters in config to the same xpath as the url for simplicity.
     * If no url path is detected, it will load a home page and if it can't resolve
     * the provided path it'll generate a 404 page.
     */
    public function parseUrl()
    {

        if(empty($uri)) $this->get('homepage_uripath');
        $uri = explode('/', substr($_SERVER['REQUEST_URI'], 1));

        if($uri[0])
        {

            if(isset($this->modules[$uri[0]]))
            {
                $module = $this->modules[$uri[0]];

                if(isset($module['object']))
                {

                    if(!isset($uri[1]))  $this->go($uri[0], 'indexAction'); else {

                        if(method_exists($module['object'], $uri[1].'Action'))
                        {

                            //build parameters from what's left
                            $params = false; $i = 2;
                            while($i !== false)
                            {
                                if(isset($uri[$i])) {

                                    $params[$uri[$i]] = false;
                                    if(isset($uri[$i+1])) $params[$uri[$i]] = $uri[$i+1];
                                    $this->set($uri[0].DS.$uri[1].DS.$uri[$i], $params[$uri[$i]]); // sets in config as module/action/var
                                    $i = $i + 2;

                                } else $i = false;
                            }

                            $action = $uri[1].'Action';

                            $this->go($uri[0], $action, $params);
                            // $module['object']->$action($params);

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

    /**
     * Generates and outputs final HTML completing the browser request. This also sets the
     * response code to a successful status.
     *
     * @param null $themename as themedirname, allows you to set a custom theme
     */

    public function renderOutput($themename = null)
    {

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

    /**
     * Sets up a 404 page state when renderOutput is called. This is useful because
     * this templates what's required to programmatically setup a page.
     */
    public function set404()
    {
        $this->log('file not found: '.$_SERVER['REQUEST_URI']);

        $this->set('site/response_code', 404);

        $this->setHook('title', 'file not found');
        $this->setHook('content', $this->get('paths/pagesdir').'404.phtml');
    }

    /**
     * A more robust dumpDebug that looks good in a browser
     */
    public function dumpDebug()
    {

        echo '<pre id = "debug"><ul>';
        echo '<li><h1>'.self::APPNAME.'</h1></li>';
        echo '<ul><li><h2>Hooks</h2>';
            var_dump($this->hooks);
        echo '<li><h2>Config</h2>';
            var_dump($this->config);
        echo '<li><h2>Modules</h2>';
            var_dump($this->modules);
        echo '</ul></ul></pre>';

    }


}