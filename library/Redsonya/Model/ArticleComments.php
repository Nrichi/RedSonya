<?php

/**
 * Redsonya_Model_ArticleComments
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class Redsonya_Model_ArticleComments extends Redsonya_Model_BaseArticleComments
{
	public function setUp()
	{
		$this->hasOne('Redsonya_Model_Articles', array(
				'local' => 'article_id',
				'foreign' => 'article_id'
		)
		);
	}
}