<?php

/**
 * Redsonya_Model_BaseMclasses
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $mc_id
 * @property string $title
 * @property string $description
 * @property integer $date
 * @property string $place
 * @property integer $created
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
abstract class Redsonya_Model_BaseMclasses extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('mclasses');
        $this->hasColumn('mc_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('title', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('date', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('place', 'string', null, array(
             'type' => 'string',
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('created', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

}