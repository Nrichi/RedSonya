<?php

class Shop_MaterialsController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->doctype('XHTML1_STRICT');
	}
	
public function indexAction()
    {
    	//Фильтры и валидаторы для данных из GET запроса
    	$filters = array(
    			'page' => array('HtmlEntities', 'StripTags', 'StringTrim'));
    	$validators = array(
    			'page' => array('Int'));
    	$input = new Zend_Filter_Input($filters, $validators);
    	$input->setData($this->getRequest()->getParams());
    	
    	//проверка корректности входных данных
    	if ($input->isValid()) {
    		$q = Doctrine_Query::create()
    			->from('Redsonya_Model_Items i')
    			->where('i.sold = ?', '0')
    			->addwhere('i.category_id = ?', '5');	//выбираем материалы из 5-й категории;
    		
    		$perPage = 10;
    		$numPageLinks = 5;
    		
    		//инициализация компонента разбиения на страницы
    		$pager = new Doctrine_Pager($q, $input->page, $perPage);
    		
    		//выполняем запрос учитывающий номер страницы
    		$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
    		
    		//инициализация макета компонанта для разбиения на страницы
    		$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
    		$pagerUrlBase = $this->view->url(array(), 'shop-materials', 1) . "/{%page}";
    		$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
    		
    		//устанавливаем шаблон для отображения ссылки на страницу
    		$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
    		$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
    		$pagerLayout->setSeparatorTemplate('&nbsp;');
    		
    		//присваиваем значения переменным представления
    		$this->view->items = $result;
    		$this->view->pages = $pagerLayout->display(null, true);   				
    	} else {
    		throw new Zend_Controller_Action_Exception('Invalid input');
    	}
    }
    
	public function displayAction()
    {
    	//фильтруем GET запрос
    	$filters = array(
    			'id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
    	$validators = array(
    			'id' => array('NotEmpty', 'Int'));
    	$input = new Zend_Filter_Input($filters, $validators);
    	$input->setData($this->getRequest()->getParams());
    	 
    	//если передан правильный идентификатор поста, создаем запрос к бд и передаем результат в вид
    	if ($input->isValid()) {
    		$q = Doctrine_Query::create()
    		->from('Redsonya_Model_Items i')
    		->where('i.item_id = ?', $input->id);
    	
    		$result = $q->fetchArray();
    		if (count($result) == 1) {
    			$this->view->item = $result[0];
    		} else {
    			throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
    		}
    		
    		$q = Doctrine_Query::create()
    		->from('Redsonya_Model_ItemImg g')
    		->where('g.item_id = ?', $input->id);
    		 
    		$result = $q->fetchArray();
    		$this->view->images = $result;
    	
    	} else {
    		throw new Zend_Controller_Action_Exception('Страница не существует', 404);
    	}
    }
}