<?php
class Redsonya_Form_McAddBlock extends Zend_Form //добавляет новый блок в мастер-класс
{
  
	protected $_mcid;
	
	public function __construct($mcid, $options=null)
	{
		parent::__construct($options);
		$this->setMcid($mcid);
		$this->init();
	}
	
	public function setMcid($mcid)
	{
		$this->_mcid = $mcid;
		return $this;
	}
	
	public function init()
	{
		// initialize form
		$this->setAction('/admin/mclasses/display/'.$this->_mcid)
	         ->setMethod('post')
	         ->setAttrib('enctype', 'multipart/form-data');
//идентификатор мастер-класса		
		$mc_id = new Zend_Form_Element_Hidden('mc_id');
		$mc_id->setValue($this->_mcid); //идентификатор поста
//фото
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
//submit button
	    $submit = new Zend_Form_Element_Submit('submit');
	    $submit->setLabel('Добавить блок')
	           ->setOptions(array('class' => 'green'));
	                
	    // attach elements to form
	    $this->addElement($mc_id)
			 ->addElement($image)
	         ->addElement($description)
	    	 ->addElement($submit);
	}
}