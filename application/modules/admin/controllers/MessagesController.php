<?php
class Admin_MessagesController extends Zend_Controller_Action
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
				//вывод списка всех сообщений
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Messages m')
					->orderBy('m.created DESC');
				$perPage = 20;
				$numPageLinks = 5;
			
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
			
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
			
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-messages-index', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
			
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
			
				//присваиваем значения переменным представления
				$this->view->inbox = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function answerAction()
	{
		$form = new Redsonya_Form_AnswerUserMsg();
		$this->view->form = $form;
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->getRequest()->getPost())) {
				$values = $form->getValues();
		
				//отправляем ответ на e-mail пользователя используя для отправки собственный эл.адрес (defaultEmailAddress) администратора 
				//из файла bootstrap
				$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
				$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
				$from = $localConfig->global->defaultEmailAddress;
				$mail = new Zend_Mail('UTF-8');
				$mail->setBodyText($values['answer']);
				$mail->setFrom($from, $values['master']);
				$mail->addTo($values['email']);
				$mail->setSubject('Ответ на Ваше сообщение с контактной формы сайта "Рыжая Соня"');

				$mail->send();
				$this->_helper->getHelper('FlashMessenger')->addMessage('Ваше сообщениепользователю <i>'.$values['name'].'</i> успешно отправлено.');
				$this->_redirect('/admin/messages/index');
			}
		}  else {
			// if GET request
			// pre-populate form
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
			if ($input->isValid()) {
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Messages m')
					->where('m.message_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->form->populate($result[0]);
					$this->view->user_msg = $result[0];
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
				->delete('Redsonya_Model_Messages m')
				->where('m.message_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении сообщения пользователя из базы данных.');

				$this->_helper->getHelper('FlashMessenger')->addMessage('Сообщение было успешно удалено.');
				$this->_redirect('/admin/messages/index');
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
		
			//если передан правильный идентификатор сообщения, получаем имя отправителя и отправляем в вид для подтверждения
			if ($input->isValid()) {
				$msg_id = $input->id;
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Messages m')
				->where('m.message_id = ?', $msg_id);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->msg = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}