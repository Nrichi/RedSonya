<?php
class Redsonya_Form_AnswerUserOrder extends Redsonya_Form_AnswerUserMsg
{
  public function init()
  {
  	// get parent form
  	parent::init();
  	
  	$this->setAction('/admin/orders/answer');
  	
  	$this->removeElement('name');
  	$this->removeElement('id');
  }
}