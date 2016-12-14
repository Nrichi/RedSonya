<?php
class Redsonya_Form_AnswerUserMsg extends Redsonya_Form_Message
{
  public function init()
  {
    // initialize form
    $this->setAction('/admin/messages/answer')
         ->setMethod('post');

    $id = new Zend_Form_Element_Hidden('message_id');
    $id->addValidator('Int')
    ->addFilter('HtmlEntities')
    ->addFilter('StringTrim');
    
    $name = new Zend_Form_Element_Text('name');
    $name->setLabel('Кому:')
         ->setOptions(array('size' => '35'))
         ->setRequired(true)
         ->addValidator('NotEmpty', true, array('messages' => array(
                 Zend_Validate_NotEmpty::IS_EMPTY
                 => "Вы не ввели имя")
           ))
         ->addValidator('Alpha', true, array('messages' => array(
                 Zend_Validate_Alpha::INVALID
                 => "Вы ввели неправильное имя",
                 Zend_Validate_Alpha::NOT_ALPHA
                 => "Имя должно состоять только из букв",
                 Zend_Validate_Alpha::STRING_EMPTY
                 => "Вы не ввели имя")
           ))            
         ->addFilter('HtmlEntities')
         ->addFilter('StripTags')
         ->addFilter('StringTrim');            
    
    // create text input for email address
    $email = new Zend_Form_Element_Text('email');
    $email->setLabel('Куда (E-mail):');
    $email->setOptions(array('size' => '50'))
          ->setRequired(true)
          ->addValidator('NotEmpty', true, array('messages' => array(
                 Zend_Validate_NotEmpty::IS_EMPTY
                 => "Вы не ввели E-mail")))
          ->addValidator('EmailAddress', true, array('messages' => array(
                  Zend_Validate_EmailAddress::INVALID
                  => "Неправильный адресс электронной почты",
                  Zend_Validate_EmailAddress::INVALID_FORMAT
                  => "Неверный формат электронной почты",
                  Zend_Validate_EmailAddress::INVALID_HOSTNAME
                  =>"Неверный формат хоста в адресе электронной почты",
                  Zend_Validate_EmailAddress::INVALID_LOCAL_PART
                  => "Неверный формат имени пользователя в адресе электронной почты",
                  Zend_Validate_EmailAddress::LENGTH_EXCEEDED
                  => "Слишком длинный адрес электронной почты")
            ))            
          ->addFilter('HtmlEntities')
          ->addFilter('StripTags')
          ->addFilter('StringToLower')        
          ->addFilter('StringTrim');            
    
	//от кого
    $master = new Zend_Form_Element_Text('master');
    $master->setLabel('От кого (ваше имя):')
		->setOptions(array('size' => '35'))
		->setRequired(true)
		->addValidator('NotEmpty', true, array('messages' => array(
			Zend_Validate_NotEmpty::IS_EMPTY
			=> "Вы не ввели имя")
		))
		->addValidator('Alpha', true, array('messages' => array(
			Zend_Validate_Alpha::INVALID
			=> "Вы ввели неправильное имя",
			Zend_Validate_Alpha::NOT_ALPHA
          	=> "Имя должно состоять только из букв",
          	Zend_Validate_Alpha::STRING_EMPTY
          	=> "Вы не ввели имя")
		))
        ->addFilter('StripTags')
        ->addFilter('StringTrim');
          
    $answer = new Zend_Form_Element_Textarea('answer');
    $answer->setLabel('Сообщение:')
            ->setOptions(array('rows' => '8','cols' => '40'))
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array(
                 Zend_Validate_NotEmpty::IS_EMPTY
                 => "Вы не ввели текст сообщения")))
            ->addFilter('HtmlEntities')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');            
    
            
    // create submit button
    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('ОТПРАВИТЬ')
           ->setOptions(array('class' => 'green'));
                
    // attach elements to form
    $this->addElement($id)
    	 ->addElement($name)
         ->addElement($email)
         ->addElement($master)
         ->addElement($answer)
    	 ->addElement($submit);
    
  }
}