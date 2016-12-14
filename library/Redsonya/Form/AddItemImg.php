<?php
class Redsonya_Form_AddItemImg extends Zend_Form
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
		$this->setAction('/admin/items/images/add/'.$this->_itemid)
	         ->setMethod('post');
		
		$id = new Zend_Form_Element_Hidden('item_id');
		$id->setValue($this->_itemid); //идентификатор товара

		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (800х600 px):')
			->setRequired(true)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '1MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
				'minwidth'  => 800,
				'minheight' => 600,
				'maxwidth'  => 800,
				'maxheight' => 600
			))
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не загрузили фото")
			))
			->setValueDisabled(true);

	    $submit = new Zend_Form_Element_Submit('submit');
	    $submit->setLabel('Сохранить')
	           ->setOptions(array('class' => 'green'));
	                
	    // attach elements to form
	    $this->addElement($id)
			 ->addElement($image)
	    	 ->addElement($submit);
	}
}