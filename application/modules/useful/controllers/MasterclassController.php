<?php

class Useful_MasterclassController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->doctype('XHTML1_STRICT');
	}
	
	public function indexAction()
	{
		/*if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}*/
	
			//Фильтры и валидаторы для данных из GET запроса
			$filters = array('page' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('page' => array('Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
	
			if ($input->isValid()) {
				//вывод списка всех мастер-классов
				$q = Doctrine_Query::create()
				->select('m.*, i.img as img, i.description as desc')
				->from('Redsonya_Model_Virtualmc m')
				->leftJoin('m.Redsonya_Model_VirtualmcImg i')
				->where('m.mc_id = i.mc_id')
				//->groupBy('o.status')
				->orderBy('m.created DESC, i.img_id ASC');
	
				$perPage = 10;
				$numPageLinks = 5;
	
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
	
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
	
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'masterclass', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
	
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
	
				//присваиваем значения переменным представления
				$this->view->mclasses = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function displayAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
	
			//отображаем мастер-класс
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
			if ($input->isValid()) {
				//получаем информацию о мк
				$mclass = Doctrine::getTable('Redsonya_Model_Virtualmc')->find($input->id);
				$this->view->mclass = $mclass;
					
				//получаем все блоки с картинками
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_VirtualmcImg i')
				->where('i.mc_id = ?', $input->id)
				->orderBy('i.img_id ASC');
				$result = $q->fetchArray();
				$this->view->blocks = $result;
			} else {
				throw new Zend_Controller_Action_Exception('Страница не существует', 404);
			}
	}
}