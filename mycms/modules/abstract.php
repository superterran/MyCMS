<?php
/**
 * MyCMS Modules Abstract
 *
 * You can extend off of this to make your own module, please see the example
 * module for details.
 *
 * Just remember that your module has to implement the abstract methods before it will
 * work.
 *
 * @package MyCMS
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 *
 */

/**
 * This implements the most of the required methods to make a basic module.
 */
abstract class mycms_modules_abstract implements mycms_module
{
    /**
     * @var $_app instance of main class
     */
    protected $_app;

    /**
     * This is the main action used when the module is fired
     * without any other arguments. This needs to be implemented
     * in order to use the module.
     *
     * @param null|array $param  arguments passed from controller (optional)
     * @return mixed
     */
    abstract public function indexAction();

    public function init()
    {
        return $this;
    }

    /**
     * populates $this->_app for use by everyone
     * @param $app instance of the parent/framework class
     */
    public function __construct($app)
    {
        $this->_app = $app;
    }

    /**
     * return the main framework class
     * @return instance|parent
     */
    public function app()
    {
        return $this->_app;
    }

}