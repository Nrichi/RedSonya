<?php
class Redsonya_Form_EventUpdate extends Redsonya_Form_Event
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/admin/events/update');
		
		$this->removeElement('image');
		
		$id = new Zend_Form_Element_Hidden('post_id');
		$id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
		
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (450х300 px):')
		->setRequired(false)		//при обновлении необязательно загружать новое фото
		->setOptions(array('class' => 'file_1'))
		->addValidator('Size', false, '1MB')
		->addValidator('Extension', false, 'jpg,jpeg,png,gif')
		->addValidator('ImageSize', false, array(
				'minwidth'  => 200,
				'minheight' => 200,
				'maxwidth'  => 500,
				'maxheight' => 500
		))
		->setValueDisabled(true);
		
		$this->addElement($id)
			->addElement($image);
	}
}