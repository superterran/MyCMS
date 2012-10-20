<?php

class mycms_modules_eav_mysql extends mycms_modules_eav
{

    public $db = false;

    public function __construct()
    {


        var_dump($this->app());

        $this->db = $this->connect($this->app()->get('database/mysql'));

    }


    public function connect($cred)
    {

        return mysql_connect($cred['host'], $cred['user'], $cred['pass'], $cred['database']);

    }

}