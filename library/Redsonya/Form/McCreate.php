<?php
class Redsonya_Form_McCreate extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/mclasses/create')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');

//название мастер-класса или статьи		
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel('Название (150 симв.):')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели название")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
//фотография
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото:')
			->setRequired(true)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '2MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
				'minwidth'  => 100,
				'minheight' => 100,
				'maxwidth'  => 2000,
				'maxheight' => 2000
			))
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не загрузили фото")
			))
			->setValueDisabled(true);

//описание
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Описание фото:')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели описание для фото")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($title)
			->addElement($image)
			->addElement($description)
			->addElement($submit);
	}
}