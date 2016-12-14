<?php
class Admin_LinksController extends Zend_Controller_Action
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
				//вывод списка ссылок
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Links l');
				$perPage = 20;
				$numPageLinks = 5;
		
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
		
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
		
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-links-index', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
		
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
		
				//присваиваем значения переменным представления
				$this->view->links = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_Link();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
		
				$item = new Redsonya_Model_Links();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				//заполняем модель и сохраняем в базу
				$item->link_id = NULL;
				$item->save();
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая ссылка была успешно добавлена.');
				$this->_redirect('/admin/links/index');
			}
		}
	}
	
	public function updateAction()
	{
		$form = new Redsonya_Form_LinkUpdate();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы

				$item = Doctrine::getTable('Redsonya_Model_Links')->find($input['link_id']);	//находим строку по id-шнику, которую нужно обновить
				$item->fromArray($input);					//заполняем строку новыми данными из формы
				$item->save();								//обновляем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Ссылка была успешно обновлена.');
				$this->_redirect('/admin/links/index');
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
				->from('Redsonya_Model_Links l')
				->where('l.link_id = ?', $input->id);
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
				->delete('Redsonya_Model_Links l')
				->where('l.link_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении ссылки из базы данных. Запись не найдена');
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Ссылка была успешно удалена.');
				$this->_redirect('/admin/links/index');
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
		
			//если передан правильный идентификатор ссылки, находим ее и передаем результат в вид
			if ($input->isValid()) {
				$linkid = $input->id;
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Links l')
				->where('l.link_id = ?', $linkid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->link = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}