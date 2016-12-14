<?php
class Admin_OrdersController extends Zend_Controller_Action
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
				//вывод списка всех заказов
				$q = Doctrine_Query::create()
					->select('o.*, i.item_name as item, i.img as img')
					->from('Redsonya_Model_Orders o')
					->leftJoin('o.Redsonya_Model_Items i')
					->where('o.item_id = i.item_id')
					//->groupBy('o.status')
					->orderBy('o.created DESC');
				$perPage = 20;
				$numPageLinks = 5;
					
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
					
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
					
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-orders-index', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
					
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
					
				//присваиваем значения переменным представления
				$this->view->orders = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function completeAction()
	{
		//фильтруем GET запрос
		$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id' => array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		
		//если передан правильный идентификатор заказа, меняем статус в базе на "выполнен"
		if ($input->isValid()) {
			$order = Doctrine::getTable('Redsonya_Model_Orders')->find($input->id);
			$order->status = 1;
			$order->save();

			$this->_helper->getHelper('FlashMessenger')->addMessage('Статус заказа был успешно обновлен.');
			$this->_redirect('/admin/orders/index');
		}
	}
	
	public function incompleteAction()
	{
		//фильтруем GET запрос
		$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id' => array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		
		//если передан правильный идентификатор заказа, меняем статус в базе на "выполнен"
		if ($input->isValid()) {
			$order = Doctrine::getTable('Redsonya_Model_Orders')->find($input->id);
			$order->status = 0;
			$order->save();
		
			$this->_helper->getHelper('FlashMessenger')->addMessage('Статус заказа был успешно обновлен.');
			$this->_redirect('/admin/orders/index');
		}
	}
	
	public function answerAction()
	{
		$form = new Redsonya_Form_AnswerUserOrder();
		$this->view->form = $form;
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->getRequest()->getPost())) {
				$values = $form->getValues();
		
				//отправляем сообщение на e-mail пользователя используя для отправки собственный эл.адрес (defaultEmailAddress) администратора
				//из файла bootstrap
				$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
				$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
				$from = $localConfig->global->defaultEmailAddress;
				$mail = new Zend_Mail('UTF-8');
				$mail->setBodyText($values['answer']);
				$mail->setFrom($from, $values['master']);
				$mail->addTo($values['email']);
				$mail->setSubject('Сообщение с сайта "Рыжая Соня" по поводу Вашего заказа');
		
				$mail->send();
				$this->_helper->getHelper('FlashMessenger')->addMessage('Ваше сообщениепользователю успешно отправлено.');
				$this->_redirect('/admin/orders/index');
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
				->from('Redsonya_Model_Orders o')
				->where('o.order_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->form->populate($result[0]);
					$this->view->order = $result[0];
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
			$del = $this->getRequest()->getPost('id');

			//удаляем запись из базы
			$q = Doctrine_Query::create()
			->delete('Redsonya_Model_Orders o')
			->where('o.order_id = ?', $del);
			$result = $q->execute();

			$this->_helper->getHelper('FlashMessenger')->addMessage('Заказ был успешно удален');
			$this->_redirect('/admin/orders/index');
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
	
			//если передан правильный идентификатор заказа, находим его имя и передаем результат в вид
			if ($input->isValid()) {
				$orderid = $input->id;
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Orders o')
				->where('o.order_id = ?', $orderid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->order = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}