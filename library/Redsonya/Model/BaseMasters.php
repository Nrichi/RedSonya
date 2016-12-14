<?php

/**
 * Redsonya_Model_BaseMasters
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $master_id
 * @property string $name
 * @property string $img
 * @property string $resume
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
abstract class Redsonya_Model_BaseMasters extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('masters');
        $this->hasColumn('master_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('name', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('img', 'string', 100, array(
             'type' => 'string',
             'length' => 100,
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('resume', 'string', null, array(
             'type' => 'string',
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

}