<?php
class Redsonya_Form_Message extends Zend_Form
{
  public function init()
  {
    // initialize form
    $this->setAction('/contact/index')
         ->setMethod('post');
         
    // create text input for name 
    $name = new Zend_Form_Element_Text('name');
    $name->setLabel('Имя:')
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
    $email->setLabel('E-mail:');
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
    
    // create text input for message body
    $message = new Zend_Form_Element_Textarea('text');
    $message->setLabel('Сообщение:')
            ->setOptions(array('rows' => '8','cols' => '40'))
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => array(
                 Zend_Validate_NotEmpty::IS_EMPTY
                 => "Вы не ввели текст сообщения")))
            ->addFilter('HtmlEntities')
            ->addFilter('StripTags')
            ->addFilter('StringTrim');            
    
    // create captcha
/*    $captcha = new Zend_Form_Element_Captcha('captcha', array(
      'captcha' => array(
        'captcha' => 'Image',
        'wordLen' => 6,
        'timeout' => 300,
        'width'   => 100,
        'height'  => 50,
        'imgUrl'  => '/captcha',
        'imgDir'  => APPLICATION_PATH . '/../public/captcha',
        'font'    => APPLICATION_PATH . '/../public/fonts/LiberationSansRegular.ttf',
        )
    ));
    $captcha->setLabel('Введите код с картинки:');  */  
            
    // create submit button
    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('ОТПРАВИТЬ')
           ->setOptions(array('class' => 'green'));
                
    // attach elements to form
    $this->addElement($name)
         ->addElement($email)
         ->addElement($message)
		 /*->addElement($captcha)*/
    	 ->addElement($submit);
    
  }
}