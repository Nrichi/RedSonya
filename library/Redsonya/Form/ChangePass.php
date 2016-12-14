<?php
class Redsonya_Form_ChangePass extends Zend_Form
{
	public function init()
	{
		$this->setAction('/admin/settings/changepass')
			->setMethod('post');
		
		$username = new Zend_Form_Element_Text('username');
		$username->setLabel('Ваш логин:')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addValidator('Alnum')
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$oldpass = new Zend_Form_Element_Text('oldpass');
		$oldpass->setLabel('Старый пароль:')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$pass1 = new Zend_Form_Element_Text('password1');
		$pass1->setLabel('Новый пароль:')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$pass2 = new Zend_Form_Element_Text('password2');
		$pass2->setLabel('Новый пароль (повтор):')
			->setOptions(array('size' => '30'))
			->setRequired(true)
			->addFilter('HtmlEntities')
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$clear = new Zend_Form_Element_Reset('clear');
		$clear->setLabel('Очистить')
			->setOptions(array('class' => 'gray sbmt'));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($username)
			->addElement($oldpass)
			->addElement($pass1)
			->addElement($pass2)
			->addElement($clear)
			->addElement($submit);
	}
}