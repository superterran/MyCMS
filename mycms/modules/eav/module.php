<?php

class mycms_modules_eav extends mycms_modules_abstract
{

    public $model = false;

    public function __construct()
    {

        include('mysql.php');
        $this->model = new mycms_modules_eav_mysql();



    }


    public function indexAction()
    {

        return false;

    }

}