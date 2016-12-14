<?php
class Redsonya_Form_MasterUpdate extends Redsonya_Form_Master
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/admin/masters/update');
		
		$this->removeElement('image');
		
		$id = new Zend_Form_Element_Hidden('master_id');
		$id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
		
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (300х450 px):')
		->setRequired(false)		//при обновлении необязательно загружать новое фото
		->setOptions(array('class' => 'file_1'))
		->addValidator('Size', false, '1MB')
		->addValidator('Extension', false, 'jpg,jpeg,png,gif')
		->addValidator('ImageSize', false, array(
				'minwidth'  => 290,
				'minheight' => 440,
				'maxwidth'  => 310,
				'maxheight' => 460
		))
		->setValueDisabled(true);
		
		$this->addElement($id)
			->addElement($image);
	}
}