<?php
class Redsonya_Form_ItemComment extends Redsonya_Form_Comment
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/gallery/display/'.$this->_postid);
		
		$this->removeElement('postid');
		
		$itemid = new Zend_Form_Element_Hidden('item_id');
		$itemid->setValue($this->_postid); //идентификатор товара
		
		$this->addElement($itemid);
	}
}