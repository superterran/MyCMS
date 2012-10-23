<?php
/**
 * A MySQL connector for our EAV Model
 */
/**
 * A MySQL connector for our EAV Model
 *
 * @package MyCMS
 * @subpackage EAV Module
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */
class mycms_modules_eav_mysql extends mycms_modules_eav
{

    public $db = false;

    public function __construct()
    {


      //  var_dump($this->app());

     //   $this->db = $this->connect($this->get('database/mysql'));

    }


    public function connect($cred)
    {

        return mysql_connect($cred['host'], $cred['user'], $cred['pass'], $cred['database']);

    }

}