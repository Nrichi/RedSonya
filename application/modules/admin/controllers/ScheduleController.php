<?php
class Admin_ScheduleController extends Zend_Controller_Action
{
	public function init() 
	{
		$this->view->doctype('XHTML1_STRICT');
	}
    
	// action to handle admin URLs
	public function preDispatch() 
	{
		$url = $this->getRequest()->getRequestUri();
	    $this->_helper->layout->setLayout('admin');          
	    if (!Zend_Auth::getInstance()->hasIdentity()) {
			$session = new Zend_Session_Namespace('redsonya.auth');
			$session->requestURL = $url;
			$this->_redirect('/admin');
		}
	}
	
	public function indexAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
		
			//Фильтры и валидаторы для данных из GET запроса
			$filters = array('page' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('page' => array('Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			if ($input->isValid()) {
				//вывод расписания мастерклассов
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Mclasses m')
				->orderBy('m.date DESC');
				$perPage = 10;
				$numPageLinks = 5;
		
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
		
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
		
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-schedule-index', 1) . "/{%page}";
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
	
	public function createAction()
	{
		$form = new Redsonya_Form_Schedule();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				
				//форматируем дату, переводя ее в метку Unix
				$day 	= $this->getRequest()->getPost('day');
				$month 	= $this->getRequest()->getPost('month');
				$year 	= $this->getRequest()->getPost('year');
				$date = mktime(1, 1, 1, $month, $day, $year);
				
				$item = new Redsonya_Model_Mclasses();
				$item->fromArray($input);					//заполняем строку новыми данными из формы

				//заполняем модель и сохраняем в базу
				$item->mc_id = NULL;
				$item->date = $date;
				$item->created = time();
				$item->save();
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая запись была успешно добавлена в расписание мастер-классов.');
				$this->_redirect('/admin/schedule/index');
			}
		}
	}
	
	public function updateAction()
	{
		$form = new Redsonya_Form_ScheduleUpdate();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				
				//форматируем дату, переводя ее в метку Unix
				$day 	= $this->getRequest()->getPost('day');
				$month 	= $this->getRequest()->getPost('month');
				$year 	= $this->getRequest()->getPost('year');
				$date = mktime(1, 1, 1, $month, $day, $year);
				
				$item = Doctrine::getTable('Redsonya_Model_Mclasses')->find($input['mc_id']);	//находим строку по id-шнику, которую нужно обновить
				$created = $item['created'];
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				$item->created = $created;
				$item->date = $date;
				$item->save();								//обновляем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Запись в расписании была успешно обновлена.');
				$this->_redirect('/admin/schedule/index');
			}
		} else {
			// if GET request
			// pre-populate form
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
			if ($input->isValid()) {
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Mclasses m')
				->where('m.mc_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->form->populate($result[0]);
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			} else {
				throw new Zend_Controller_Action_Exception('Страница не существует');
			}
		}
	}
	
	public function deleteAction()
	{
		if ($this->getRequest()->isPost()) {
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			if ($input->isValid()) {
		
				$del = $this->getRequest()->getPost('id');

				//удаляем запись из базы
				$q = Doctrine_Query::create()
				->delete('Redsonya_Model_Mclasses m')
				->where('m.mc_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении мастер-класса из базы данных. Запись не найдена');
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Запись была успешно удалена из расписания.');
				$this->_redirect('/admin/schedule/index');
			}
			else {
				throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
			}
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			//если передан правильный идентификатор записи о мастер-классе, находим ее название и передаем результат в вид
			if ($input->isValid()) {
				$mcid = $input->id;
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Mclasses m')
				->where('m.mc_id = ?', $mcid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->mc = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}