<?php
class Redsonya_Form_ForgotPass extends Zend_Form
{
	public function init()
	{
		$this->setAction('/admin/forgotpass')
			->setMethod('post');
		
		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('Логин:')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addValidator('Alnum')
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Получить пароль')
			->setOptions(array('class' => 'green'));
		
		$this->addElement($username)
			->addElement($submit);
	}
}