<?php
class Redsonya_Form_LinkUpdate extends Redsonya_Form_Link
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/admin/links/update');

		$id = new Zend_Form_Element_Hidden('link_id');
		$id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');

		$this->addElement($id);
	}
}