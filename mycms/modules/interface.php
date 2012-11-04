<?php
/**
 * This is an interface defining the basic construction of a module. The abstract
 * implements most of this with some supporting logic, leaving the module to implement
 * the rest. This interface is a solid starting point if you want to build this from scratch.
 *
 * @package MyCMS
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */
/**
 * This specifies what the framework expects for a working module implementation.
 */
interface mycms_module {

    /**
     * Returns an instance of mycms, use this to communicate with the main app and do stuff like setting hooks
     *
     * @return instance of mycms
     */
    public function app();

    /**
     * This is ran after module initiation, allows for a module to handle things like
     * user authentication. This is sort of like a constructor, except this is called by
     * the framework so to separate module instantiation and initialization
     *
     * @see mycms::loadModules()
     * @return mixed;
     */
    public function init();

    /**
     * This is the default controller action, You will probably need more than just this.
     * in mycms_http url parser will translate the url to a proper module/controller
     * call and use mycms_framework::go() to call the module.
     *
     * @see mycms_http::parseUrl()
     * @see mycms::go()
     * @return mixed
     */

     public function indexAction();

}