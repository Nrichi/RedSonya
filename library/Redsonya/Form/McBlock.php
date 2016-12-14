<?php
class Redsonya_Form_McBlock extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/mclasses/editblock')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');

//идентификатор фотографии с описанием		
		$id = new Zend_Form_Element_Hidden('img_id');
		$id->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
		
//идентификатор мастер-класса
		$mcid = new Zend_Form_Element_Hidden('mc_id');
		$mcid->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');		
		
//фотография
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото:')
			->setRequired(false)
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

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($id)
			->addElement($mcid)
			->addElement($image)
			->addElement($description)
			->addElement($submit);
	}
}