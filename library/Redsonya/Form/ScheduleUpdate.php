<?php
class Redsonya_Form_ScheduleUpdate extends Redsonya_Form_Schedule
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/admin/schedule/update');
		
		$this->removeElement('image');
		
		$id = new Zend_Form_Element_Hidden('mc_id');
		$id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');

		$this->addElement($id);
	}
}