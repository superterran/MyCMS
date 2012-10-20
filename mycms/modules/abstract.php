<?php

abstract class mycms_modules_abstract implements mycms_module
{

    abstract public function indexAction();


    public function init()
    {
        return $this;
    }

    public function app()
    {
        return mycms::getInstance();
    }

}