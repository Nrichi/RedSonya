<?php
class Redsonya_Form_Award extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/awards/create')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');
		
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel('Название (150 симв.):')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели название награды (выставки)")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
			
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (100x100 px):')
			->setRequired(true)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '2MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
					'minwidth'  => 100,
					'minheight' => 100,
					'maxwidth'  => 200,
					'maxheight' => 200
			))
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не загрузили фото")
			))
			->setValueDisabled(true);
			
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($title)
			->addElement($image)
			->addElement($submit);
	}
}