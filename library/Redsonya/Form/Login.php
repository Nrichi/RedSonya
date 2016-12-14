<?php
class Redsonya_Form_Login extends Zend_Form
{
	public function init()
	{
		$this->setAction('/admin')
			->setMethod('post');
		
		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('Логин:')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addValidator('Alnum')
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$pass = new Zend_Form_Element_Password('password');
		$pass->setLabel('Пароль:')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$clear = new Zend_Form_Element_Reset('clear');
		$clear->setLabel('Очистить')
			->setOptions(array('class' => 'gray sbmt'));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Вход')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($username)
			->addElement($pass)
			->addElement($clear)
			->addElement($submit);
	}
}