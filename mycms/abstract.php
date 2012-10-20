<?php

// implements basic basic stuff


abstract class framework_abstract
{

    const CONFIG_FILE   = 'config.xml';
    const LOG_FILENAME  = 'log.txt';

    public $logfile;
    public $config_file;

    const LOGGING = true;

    public $basedir;
    public $baseurl;
    public $appdir;
    public $appurl;
    public $config;

    public function __construct()
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('TAB', "\t");
        define('BR', PHP_EOL);

        // let's populate some basic variables we'll use all the time

        $this->basedir  = $this->cap($_SERVER['DOCUMENT_ROOT']);
        $this->baseurl  = 'http://'.$_SERVER['SERVER_NAME'].DS;  //I know, I'm lazy and should be fired...

        $this->config_file = $this->appdir.self::CONFIG_FILE;
        $this->config = $this->loadConfig(); // use the get() method to get values from this

        $this->logfile  = $this->appdir.self::LOG_FILENAME;

        $this->appdir       = $this->cap($this->basedir.self::APPNAME);
        $this->appurl       = $this->baseurl.self::APPNAME.DS;


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

        var_dump($this->config);

        $this->config = array_merge_recursive($this->config, $new);

        return;

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

}