<?php

/**
 * Redsonya_Model_LinkCategories
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class Redsonya_Model_LinkCategories extends Redsonya_Model_BaseLinkCategories
{
	public function setUp()
	{
		$this->hasMany('Redsonya_Model_Links', array(
				'local' => 'category_id',
				'foreign' => 'category_id'
		)
		);
	}
}