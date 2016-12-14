<?php
class Redsonya_Form_ArticleEditBlock extends Redsonya_Form_McBlock //Zend_Form
{
	public function init()
	{
		// get parent form
		parent::init();
	
		$this->setAction('/admin/articles/editblock');
	
		$this->removeElement('mc_id');
	
	
		//идентификатор поста
		$artid = new Zend_Form_Element_Hidden('article_id');
		$artid->addValidator('Int')
		->addFilter('HtmlEntities')
		->addFilter('StringTrim');
	
		$this->addElement($artid);
	}
}