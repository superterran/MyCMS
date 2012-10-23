<?php
/**
 * This is (going to be) a basic implementation of an Eav Model
 */
/**
 * An module of a basic eav model for use with our application
 *
 * @package MyCMS
 * @subpackage EAV Module
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */
class mycms_modules_eav extends mycms_modules_abstract
{

    public $model = false;

    public function __construct()
    {

        /**
         * @todo Implement Eav Module
         */
        include('mysql.php');
        $this->model = new mycms_modules_eav_mysql();



    }


    public function indexAction()
    {

        return false;

    }

}