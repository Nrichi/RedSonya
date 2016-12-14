<?php
class Redsonya_Form_Event extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/events/create')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');
		
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel('Заголовок:')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели заголовок блока")
			))
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$post = new Zend_Form_Element_Textarea('post');
		$post->setLabel('Текст блока:')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели текст блока")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
			
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (450х300 px):')
			->setRequired(true)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '1MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
					'minwidth'  => 200,
					'minheight' => 200,
					'maxwidth'  => 500,
					'maxheight' => 500
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
			->addElement($post)
			->addElement($image)
			->addElement($submit);
	}
}