<?php
class Redsonya_Form_Master extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/masters/create')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');
		
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('ФИО мастера:')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели фамилию, имя и отчество мастера")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$resume = new Zend_Form_Element_Textarea('resume');
		$resume->setLabel('Резюме:')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели резюме мастера")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
			
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (300х450 px):')
			->setRequired(true)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '1MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
					'minwidth'  => 290,
					'minheight' => 440,
					'maxwidth'  => 310,
					'maxheight' => 460
			))
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не загрузили фото")
			))
			->setValueDisabled(true);
			
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($name)
			->addElement($resume)
			->addElement($image)
			->addElement($submit);
	}
}