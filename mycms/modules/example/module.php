<?php
/**
 * This is the example module, it doesn't do much but is provided as a usage example.
 * Please take a look at the abstract for a better understanding at what all is
 * involved with constructing a module..
 *
 * @package MyCMS
 * @subpackage Example Module
 * @url https://github.com/superterran/MyCMS
 * @author Doug Hatcher superterran@gmail.com
 * @copyright http://creativecommons.org/licenses/by/3.0/deed.en_US
 */
class mycms_modules_example extends mycms_modules_abstract
{

    public function indexAction()
    {

        $this->app()->setHook('content', "This is the Example Module's Index Action. Copy this module and start coding here");

    }

    public function urlparamAction($params)
    {

        if(isset($params['twinkle'])) {

            $this->app()->setHook(
                'content','<div style = "border: 3px dashed red; padding:20px; text-align: center; font-size: 16px; font-weight: bold">
                 Twinkle Parameter Detected, Injected this hook</div>'
            );
        }

        $this->app()->setHook('content', __DIR__.DS.'hooks/urlparam.phtml');

    }

    public function testAction()
    {

        var_dump('hoo haa');
        $this->app()->set404();

    }

}