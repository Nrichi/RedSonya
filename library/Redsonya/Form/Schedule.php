<?php
class Redsonya_Form_Schedule extends Zend_Form
{
	public function init()
	{
		// initialize form
		$this->setAction('/admin/schedule/create')
			->setMethod('post');
		
//название
		$title = new Zend_Form_Element_Text('title');
		$title->setLabel('Название (150 симв.):')
		->setOptions(array('size' => '150'))
		->setRequired(true)
		->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели название мк")
		))
		->addFilter('StripTags')
		->addFilter('StringTrim');
		
//описание
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Описание:')
		->setOptions(array('rows' => '8','cols' => '40'))
		->setRequired(true)
		->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не ввели описание мк")
		))
		->addFilter('StripTags')
		->addFilter('StringTrim');

//дата проведения
	    $day = new Zend_Form_Element_Select('day');
	    $day->setLabel('Дата проведения:')
						->setOptions(array('class' => 'select_date'))
	                    ->addValidator('Int')            
	                    ->addFilter('HtmlEntities')            
	                    ->addFilter('StringTrim')            
	                    ->addFilter('StringToUpper')
	                    ->setDecorators(array(
	                        array('ViewHelper'),
	                        array('Label', array('tag' => 'dt')),
	                        array('HtmlTag', 
	                          array(
	                            'tag' => 'div', 
	                            'openOnly' => true, 
	                            'placement' => 'prepend'
	                          )
	                        ),
	                      ));                    
	    for($x=1; $x<=31; $x++) {
	      $day->addMultiOption($x, sprintf('%02d', $x));      
	    }  
	    
	    $month = new Zend_Form_Element_Select('month');
	    $month->addValidator('Int')            
	                      ->addFilter('HtmlEntities')            
	                      ->addFilter('StringTrim')
	                      ->setOptions(array('class' => 'select_date'))
	                      ->setDecorators(array(
	                          array('ViewHelper')
	                        ));                      
	    for($x=1; $x<=12; $x++) {
	      $month->addMultiOption($x, date('M', mktime(1,1,1,$x,1,1)));      
	    }  
	    
	    $year = new Zend_Form_Element_Select('year');
	    $year->addValidator('Int')            
	                     ->addFilter('HtmlEntities')            
	                     ->addFilter('StringTrim')
	                     ->setOptions(array('class' => 'select_date'))
	                     ->setDecorators(array(
	                          array('ViewHelper'),
	                          array('HtmlTag', 
	                            array(
	                              'tag' => 'div', 
	                              'closeOnly' => true
	                            )
	                          ),
	                       ));
	    for($x=2013; $x<=2030; $x++) {
	      $year->addMultiOption($x, $x);      
	    }  

//место проведения
		$place = new Zend_Form_Element_Textarea('place');
		$place->setLabel('Место проведения:')
		->setOptions(array('rows' => '8','cols' => '40'))
		->setRequired(true)
		->addValidator('NotEmpty', true, array('messages' => array(
				Zend_Validate_NotEmpty::IS_EMPTY
				=> "Вы не указали место проведения мк")
		))
		->addFilter('StripTags')
		->addFilter('StringTrim');
			
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Сохранить')
			->setOptions(array('class' => 'green sbmt'));
		
		$this->addElement($title)
			->addElement($description)
			->addElement($day)
			->addElement($month)
			->addElement($year)
			->addElement($place)
			->addElement($submit);
	}
}