<?php
class Redsonya_Form_ItemUpdate extends Redsonya_Form_Item
{
	public function init()
	{
		// get parent form
		parent::init();
		
		$this->setAction('/admin/items/update');
		
		$this->removeElement('image');
		
		$id = new Zend_Form_Element_Hidden('item_id');
		$id->addValidator('Int')
		->addFilter('HtmlEntities')
		->addFilter('StringTrim');
		
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (920‡510 px):')
			->setRequired(false)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '2MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
				'minwidth'  => 920,
				'minheight' => 510,
				'maxwidth'  => 920,
				'maxheight' => 510
		))
		->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не загрузили основное фото")
		))
		->setValueDisabled(true);
		
		$this->addElement($id)
			->addElement($image);
	}
}