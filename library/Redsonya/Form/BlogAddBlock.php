<?php
class Redsonya_Form_BlogAddBlock extends Redsonya_Form_McAddBlock //добавляет новый блок в пост блога
{
	public function init()
	{
		// get parent form
		parent::init();
			
		$this->setAction('/admin/blog/display/'.$this->_mcid);
		
		$this->removeElement('mc_id');
		
		//идентификатор поста
		$post_id = new Zend_Form_Element_Hidden('post_id');
		$post_id->setValue($this->_mcid); //идентификатор поста
		$post_id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');

		
		$this->addElement($post_id);
			
	}
}