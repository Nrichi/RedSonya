<?php

/**
 * Redsonya_Model_BaseItems
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $item_id
 * @property string $item_name
 * @property string $title
 * @property integer $category_id
 * @property string $description
 * @property float $width
 * @property float $height
 * @property float $depth
 * @property float $weight
 * @property string $materials
 * @property string $img
 * @property integer $created
 * @property integer $price
 * @property integer $reserve
 * @property integer $sold
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
abstract class Redsonya_Model_BaseItems extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('items');
        $this->hasColumn('item_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('item_name', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('title', 'string', 150, array(
             'type' => 'string',
             'length' => 150,
             'fixed' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('category_id', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'unsigned' => 1,
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
        $this->hasColumn('width', 'float', null, array(
             'type' => 'float',
             'unsigned' => 1,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('height', 'float', null, array(
             'type' => 'float',
             'unsigned' => 1,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('depth', 'float', null, array(
             'type' => 'float',
             'unsigned' => 1,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('weight', 'float', null, array(
             'type' => 'float',
             'unsigned' => 1,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             ));
        $this->hasColumn('materials', 'string', null, array(
             'type' => 'string',
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
        $this->hasColumn('created', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('price', 'integer', 3, array(
             'type' => 'integer',
             'length' => 3,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('reserve', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('sold', 'integer', 1, array(
             'type' => 'integer',
             'length' => 1,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
    }

}