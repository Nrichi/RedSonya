<?php
class Redsonya_Form_BlogEditBlock extends Redsonya_Form_McBlock
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/admin/blog/editblock');
		
		$this->removeElement('mc_id');
		
		
//идентификатор поста
		$postid = new Zend_Form_Element_Hidden('post_id');
		$postid->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');	
		
		$this->addElement($postid);
	}
}