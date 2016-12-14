<?php
class Redsonya_Form_Category extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/mainpage/update')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');
		
		$id = new Zend_Form_Element_Hidden('category_id');
		$id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
		
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel('Название категории (не менять):')
			->setOptions(array('size' => '35'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели название категории")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
		
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Описание:')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели описание категории")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');
			
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (180х120 px):')
			->setRequired(false)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '204800')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
					'minwidth'  => 180,
					'minheight' => 120,
					'maxwidth'  => 540,
					'maxheight' => 360
			))
			->setValueDisabled(true);
			
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($id)
			->addElement($title)
			->addElement($description)
			->addElement($image)
			->addElement($submit);
	}
}