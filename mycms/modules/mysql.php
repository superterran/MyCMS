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
    const dbname = 'mycms';
    public $_tablename = false;


    public $_obj = false;


    public function __construct($app)
    {

        parent::__construct($app);

        $this->db = $this->connect($this->app()->get('database/mysql'));

    }


    public function connect($cred)
    {

        return mysql_connect($cred['host'], $cred['user'], false, $cred['database']);

    }

    public function setTablename($tablename)
    {
        $this->_tablename = self::dbname.'.'.$tablename;


        // START install check
            $results = $this->runsql('show tables from '. self::dbname);

            $table_exists = false;
            while($row = mysql_fetch_array($results))
            {
                if($row[0] == $tablename) $table_exists = true;
            }

            if(!$table_exists) $this->install();
        // END install check

    }

    public function getTablename()
    {
        return $this->_tablename;
    }

    // this is a function that should be squeezed out that'll simply
    // create the desired eav structure in the given db/table
    public function install()
    {

        $query = "
            CREATE TABLE ".$this->getTablename()." (
                  `id`  INT(11) NOT NULL AUTO_INCREMENT,
                  `pid` INT NOT NULL DEFAULT 0,
                  `entity` VARCHAR(45) NULL ,
                  `attribute` VARCHAR(45) NULL ,
                  `value` LONGTEXT NULL ,
            PRIMARY KEY (`id`));
        ";

       $this->runsql($query);

    }


    protected function runsql($query, $debug_output = false)
    {

        if($debug_output) var_dump($query);
        $data = mysql_query($query, $this->db) or die('Mysql Error: '.$query.'<br><br>'.mysql_error());

      //  var_dump($query);
        return $data;
    }

    /*
     *  How the EAV works...
     *
     *  I'm making this up as I go along....
     *
     */


    public function getData($entity, $value)
    {

        $query_e = '
            select * from '.$this->getTablename().'
            where entity = "'.$entity.'" and attribute = "'.$value.'"
        ';

        $result = $this->runsql($query_e);
        $E =  mysql_fetch_assoc($result);

        $E['slug'] = $E['value'];
        unset($E['value']);

        $query_a = '
            select * from '.$this->getTablename().'
            where pid = '.$E['id'].'
        ';

        $result = $this->runsql($query_a, true);

        while($A = mysql_fetch_array($result))
        {
            $E[$A['attribute']] = $A['value'];
        }

        return (object) $E;

    }


    public function setData($entity, $attribute, $value)
    {

        $query = '
            insert into '.$this->getTablename().' set

                entity = "'.$this->wash($entity).'",
                attribute = "'.$this->wash($attribute).'",
                value = "'.$this->wash($value).'"
        ';

        $this->runsql($query);

    }

    public function wash($string)
    {

        return $string;

    }


    public function obj($new = false)
    {
        if($new) $this->_obj = $new;
        return $this->_obj;
    }

}