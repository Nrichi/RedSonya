<?php
class Redsonya_Form_Order extends Zend_Form
{
	protected $_itemid;

	public function __construct($itemid, $options=null)
	{
		parent::__construct($options);
		$this->setItemid($itemid);
		$this->init();
	}
  
	public function setItemid($itemid) 
	{
		$this->_itemid = $itemid;
		return $this;
	}

	
	
  public function init()
  {
    // initialize form
    $this->setAction('/shop/form/'.$this->_itemid)
         ->setMethod('post');
         
	$itemid = new Zend_Form_Element_Hidden('item_id');
	$itemid->setValue($this->_itemid); //идентификатор товара
    
    $familia = new Zend_Form_Element_Text('familia');
    $familia->setLabel('Фамилия:')
    ->setOptions(array('size' => '35'))
    ->setRequired(true)
    ->addValidator('NotEmpty', true, array('messages' => array(
    		Zend_Validate_NotEmpty::IS_EMPTY
    		=> "Вы не ввели фамилию")
    ))
    ->addValidator('Alpha', true, array('messages' => array(
    		Zend_Validate_Alpha::INVALID
    		=> "Поле заполнено некорректно",
    		Zend_Validate_Alpha::NOT_ALPHA
    		=> "Фамилия должна состоять только из букв",
    		Zend_Validate_Alpha::STRING_EMPTY
    		=> "Вы не ввели фамилию")
    ))
    ->addFilter('HtmlEntities')
    ->addFilter('StripTags')
    ->addFilter('StringTrim');
    
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

     $otchestvo = new Zend_Form_Element_Text('otchestvo');
     $otchestvo->setLabel('Отчество:')
         ->setOptions(array('size' => '35'))
         ->setRequired(true)
         ->addValidator('NotEmpty', true, array('messages' => array(
         		Zend_Validate_NotEmpty::IS_EMPTY
         		=> "Вы не ввели отчество")
         ))
         ->addValidator('Alpha', true, array('messages' => array(
         		Zend_Validate_Alpha::INVALID
         		=> "Поле заполнено не корректно",
         		Zend_Validate_Alpha::NOT_ALPHA
         		=> "Отчество должно состоять только из букв",
         		Zend_Validate_Alpha::STRING_EMPTY
         		=> "Вы не ввели отчество")
         ))
         ->addFilter('HtmlEntities')
         ->addFilter('StripTags')
         ->addFilter('StringTrim');
         
    $address = new Zend_Form_Element_Text('address');
    $address->setLabel('Адрес доставки:')
         ->setOptions(array('size' => '300'))
         ->setRequired(true)
         ->addValidator('NotEmpty', true, array('messages' => array(
         		Zend_Validate_NotEmpty::IS_EMPTY
         		=> "Вы не ввели адрес доставки")
         ))
         ->addFilter('HtmlEntities')
         ->addFilter('StripTags')
         ->addFilter('StringTrim');
         
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

   //обработка поля с номером телефона       
    $tel = new Zend_Form_Element_Text('tel');
    $tel->setLabel('Телефон:');
    $tel->setOptions(array('size' => '12'))
    	->setRequired(true)
    	->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели номер телефона")))
        ->addValidator('StringLength', false, array('min' => 11, 'max' => 12))
        ->addValidator('Regex', false, array(
            'pattern'   => '/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/',
            'messages'  => array(
              Zend_Validate_Regex::INVALID    => 
                '\'%value%\' номер телефона должен соответствовать формату  +7 (YYY) XXX-XX-XX',
              Zend_Validate_Regex::NOT_MATCH  => 
                '\'%value%\' не соответствует международному формату +7 (YYY) XXX-XX-XX'
            )
          ))
        ->addFilter('HtmlEntities')            
        ->addFilter('StringTrim');
    
    // примечания
    $note = new Zend_Form_Element_Textarea('note');
    $note->setLabel('Примечание:')
            ->setOptions(array('rows' => '8','cols' => '40'))
            ->setRequired(false)
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
    $submit->setLabel('ОТПРАВИТЬ ЗАКАЗ')
           ->setOptions(array('class' => 'green'));
                
    // attach elements to form
    $this->addElement($itemid)
    	 ->addElement($familia)
    	 ->addElement($name)
    	 ->addElement($otchestvo)
    	 ->addElement($address)
         ->addElement($email)
         ->addElement($tel)
         ->addElement($note)
		 /*->addElement($captcha)*/
    	 ->addElement($submit);
    
  }
}