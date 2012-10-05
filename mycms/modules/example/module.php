<?php

class mycms_modules_example extends mycms_modules_abstract
{

    public function indexAction()
    {

        mycms::setHook('content', 'Hi guys!!');


        var_dump(mycms::getHook());
    }

}