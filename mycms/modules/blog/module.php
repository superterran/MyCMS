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
class mycms_modules_blog extends mycms_modules_eav
{

    public $model = false;

    public function indexAction()
    {
        echo '<pre>';
        /**
         * I'll figure out how this should work later
         *
         * either indexAction should return false and this should basically
         * be the data object another module works with, or we need to separate
         * out controllers and models
         */
        require_once('mysql.php'); // this problem is boring



        $this->model = new mycms_modules_eav_mysql($this->app()); // db connection?


        $this->model->setTablename('eav');


        //    $config = $this->model->setData('blog', 'posts', 'hello_world');

        $posts = $this->model->getData('blog', 'posts');


        var_dump($posts);


        // $this->model->install();


        echo '</pre>';
        return false;

    }

    public function getPostList()
    {




    }





}