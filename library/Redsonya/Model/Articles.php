<?php

/**
 * Redsonya_Model_Articles
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 6401 2009-09-24 16:12:04Z guilhermeblanco $
 */
class Redsonya_Model_Articles extends Redsonya_Model_BaseArticles
{
	public function setUp()
	{
		$this->hasMany('Redsonya_Model_ArticlesImg', array(
				'local' => 'article_id',
				'foreign' => 'article_id'
		)
		);
		
		$this->hasMany('Redsonya_Model_ArticleComments', array(
				'local' => 'article_id',
				'foreign' => 'article_id'
		)
		);
	}
}