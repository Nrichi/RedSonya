<?php

/**
 * Redsonya_Model_BaseArticlesImg
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $img_id
 * @property integer $article_id
 * @property string $img
 * @property string $description
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
abstract class Redsonya_Model_BaseArticlesImg extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('articles_img');
        $this->hasColumn('img_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => true,
             'autoincrement' => true,
             ));
        $this->hasColumn('article_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => 4,
             'unsigned' => 1,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             ));
        $this->hasColumn('img', 'string', 35, array(
             'type' => 'string',
             'length' => 35,
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
    }

}