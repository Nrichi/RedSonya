<?php

/**
 * Redsonya_Model_ItemComments
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class Redsonya_Model_ItemComments extends Redsonya_Model_BaseItemComments
{
	public function setUp()
	{
		$this->hasOne('Redsonya_Model_Items', array(
				'local' => 'item_id',
				'foreign' => 'item_id'
		)
		);
	}
}