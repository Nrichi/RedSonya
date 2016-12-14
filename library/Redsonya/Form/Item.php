<?php
class Redsonya_Form_Item extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/items/create')
			->setMethod('post')
			->setAttrib('enctype', 'multipart/form-data');

//название		
		$item_name = new Zend_Form_Element_Text('item_name');
		$item_name->setLabel('Название изделия (150 симв.):')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели название изделия")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//заголовок		
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel('Заголовок (150 симв.):')
			->setOptions(array('size' => '150'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
					Zend_Validate_NotEmpty::IS_EMPTY
					=> "Вы не ввели заголовок для описания")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//категория
		$category = new Zend_Form_Element_Select('category_id');
		$category->setLabel('Категория:')
			->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim')
			->addFilter('StringToUpper');
		foreach ($this->getCategories() as $c) {
			$category->addMultiOption($c['category_id'], $c['title']);      
		}

//описание
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Описание изделия:')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели описание товара")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//ширина
		$width = new Zend_Form_Element_Text('width');
		$width->setLabel('Ширина (см):')
			->setOptions(array('size' => '50'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели ширину изделия")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//высота
		$height = new Zend_Form_Element_Text('height');
		$height->setLabel('Высота (см):')
			->setOptions(array('size' => '50'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели высоту изделия")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//глубина
		$depth = new Zend_Form_Element_Text('depth');
		$depth->setLabel('Глубина (см):')
			->setOptions(array('size' => '50'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели глубину изделия")
			))
		->addFilter('StripTags')
		->addFilter('StringTrim');

//вес
		$weight = new Zend_Form_Element_Text('weight');
		$weight->setLabel('Вес (кг):')
			->setOptions(array('size' => '50'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели вес изделия")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//материалы
		$materials = new Zend_Form_Element_Textarea('materials');
		$materials->setLabel('Материалы (через запятую):')
			->setOptions(array('rows' => '8','cols' => '40'))
			->setRequired(true)
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не указали материалы изделия")
			))
			->addFilter('StripTags')
			->addFilter('StringTrim');

//фото
		$image = new Zend_Form_Element_File('img');
		$image->setLabel('Фото (920‡510 px):')
			->setRequired(true)
			->setOptions(array('class' => 'file_1'))
			->addValidator('Size', false, '2MB')
			->addValidator('Extension', false, 'jpg,jpeg,png,gif')
			->addValidator('ImageSize', false, array(
				'minwidth'  => 920,
				'minheight' => 510,
				'maxwidth'  => 920,
				'maxheight' => 510
		))
		->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не загрузили основное фото")
		))
		->setValueDisabled(true);

//цена
		$price = new Zend_Form_Element_Text('price');
		$price->setLabel('Цена (р.):')
			->setOptions(array('size' => '50'))
			->setRequired(true)
			->addValidator('Int')
			->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не указали цену товара")
		))
		->addFilter('StripTags')
		->addFilter('StringTrim');
			
//награды 3 селекта
		$award1 = new Zend_Form_Element_Select('award1');
		$award1->setLabel('Награда 1:')
			->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim')
			->addFilter('StringToUpper')
			->addMultiOption(null, '---');
		foreach ($this->getAwards() as $a) {
			$award1->addMultiOption($a['award_id'], $a['title']);
		}
		
		$award2 = new Zend_Form_Element_Select('award2');
		$award2->setLabel('Награда 2:')
			->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim')
			->addFilter('StringToUpper')
			->addMultiOption(null, '---');
		foreach ($this->getAwards() as $a) {
			$award2->addMultiOption($a['award_id'], $a['title']);
		}
		
		$award3 = new Zend_Form_Element_Select('award3');
		$award3->setLabel('Награда 3:')
			->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim')
			->addFilter('StringToUpper')
			->addMultiOption(null, '---');
		foreach ($this->getAwards() as $a) {
			$award3->addMultiOption($a['award_id'], $a['title']);
		}
	
//в наличии
		$sold = new Zend_Form_Element_Radio('sold');
		$sold->setLabel('В наличии:')
			->setOptions(array('class' => 'select_date'))
			->setRequired(true)
			->addValidator('Int')
			->addFilter('HtmlEntities')
			->addFilter('StringTrim');
		
		$sold->addMultiOption(1, 'нет')
			 ->addMultiOption(0, 'есть')
			 ->setSeparator('');
		$sold->setValue(0);

//submit
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($item_name)
			->addElement($title)
			->addElement($category)
			->addElement($description)
			->addElement($width)
			->addElement($height)
			->addElement($depth)
			->addElement($weight)
			->addElement($materials)
			->addElement($image)
			->addElement($price)
			->addElement($award1)
			->addElement($award2)
			->addElement($award3)
			->addElement($sold)
			->addElement($submit);
	}
	
	public function getCategories() {
		//получаем список категорий товаров
		$q = Doctrine_Query::create()
			->from('Redsonya_Model_ItemCategories c');
		return $q->fetchArray();
	}
	
	public function getAwards()
	{
		//получаем список доступных наград и передем их в селекторы формы
		$q = Doctrine_Query::create()
			->from('Redsonya_Model_Awards a')
			->orderBy('a.award_id DESC');
		return $q->fetchArray();
	}
}