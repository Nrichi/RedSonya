<?php
class Redsonya_Form_Link extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/links/create')
			->setMethod('post');
		
//описание
		$title = new Zend_Form_Element_Textarea('title');
		$title->setLabel('Описание (150 симв.):')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели описание")
		))
		->addFilter('StripTags')
		->addFilter('StringTrim');
		
//ссылка
		$link = new Zend_Form_Element_Text('link');
		$link->setLabel('Адрес ссылки(www):')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели адрес ссылки")
		))
		->addFilter('StripTags')
		->addFilter('StringTrim');

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($link)
			->addElement($title)
			->addElement($submit);
	}
}