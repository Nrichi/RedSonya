<?php
class Redsonya_Form_BlogPost extends Redsonya_Form_McCreate
{
	public function init()
	{
		// get parent form
		parent::init();
		 
		$this->setAction('/admin/blog/create');
		 
	}
}