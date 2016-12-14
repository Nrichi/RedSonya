<?php
class Redsonya_Form_ArticleAddBlock extends Redsonya_Form_McAddBlock //добавляет новый блок в статью
{
	public function init()
	{
		// get parent form
		parent::init();
			
		$this->setAction('/admin/articles/display/'.$this->_mcid);
	
		$this->removeElement('mc_id');
	
		//идентификатор поста
		$art_id = new Zend_Form_Element_Hidden('article_id');
		$art_id->setValue($this->_mcid); //идентификатор статьи
		$art_id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
	
	
		$this->addElement($art_id);
			
	}
}