<?php

class mycms_modules_example extends mycms_modules_abstract
{

    public function indexAction()
    {

        mycms::setHook('content', "This is the Example Module's Index Action. Copy this module and start coding here");

    }

}