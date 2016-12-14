<?php

class Useful_ScheduleController extends Zend_Controller_Action
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
    			->from('Redsonya_Model_Mclasses m');
    		
    		$perPage = 10;
    		$numPageLinks = 5;
    		
    		//инициализация компонента разбиения на страницы
    		$pager = new Doctrine_Pager($q, $input->page, $perPage);
    		
    		//выполняем запрос учитывающий номер страницы
    		$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
    		
    		//инициализация макета компонанта для разбиения на страницы
    		$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
    		$pagerUrlBase = $this->view->url(array(), 'schedule', 1) . "/{%page}";
    		$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
    		
    		//устанавливаем шаблон для отображения ссылки на страницу
    		$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
    		$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
    		$pagerLayout->setSeparatorTemplate('&nbsp;');
    		
    		//присваиваем значения переменным представления
    		$this->view->mclasses = $result;
    		$this->view->pages = $pagerLayout->display(null, true);   				
    	} else {
    		throw new Zend_Controller_Action_Exception('Страница не существует', 404);
    	}
    }
}