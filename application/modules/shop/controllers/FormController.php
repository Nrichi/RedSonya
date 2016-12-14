<?php
class Shop_FormController extends Zend_Controller_Action
{

	public function init()
	{
		$this->view->doctype('XHTML1_STRICT');
	}
	
	public function indexAction()
	{
		//проверяем id-шник товара
		$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id' => array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		 
		//если все правильно, отображаем название и фотку товара
		if ($input->isValid()) {
			$id = $input->id;
			$q = Doctrine_Query::create()
			->select('i.item_name, i.img, i.width, i.height, i.depth, i.weight, i.price')
			->from('Redsonya_Model_Items i')
			->where('i.item_id = ?', $id);
			
			$result = $q->fetchArray();
			if (count($result) == 1) {
				$this->view->item = $result[0];
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
			}
		} else {
	  		throw new Zend_Controller_Action_Exception('Страница не существует. Неправильный идентификатор товара', 404);
	  	}
		
	  	//создаем объект формы и передаем в скрытое поле id-шник товара через конструктор
		$form = new Redsonya_Form_Order($id);
	    $this->view->form = $form;
	    
		//запись в базу заказа после валидации данных
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->getRequest()->getPost())) {
				$order = new Redsonya_Model_Orders;
	    		$order->fromArray($form->getValues());
	    		$order->order_id = null;
	    		$order->created = time();
	    		$order->status = 0;
	    		$order->save();
	    		
	    		//если заказ успешно сохранен - отправляем уведомление на e-mail 
	    		//и выводим сообщение об успешном оформлении заказа
	    		$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
	    		$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
	    		$to = $localConfig->global->defaultEmailAddress;
	    		$mail = new Zend_Mail('UTF-8');
	    		$mail->setBodyText('На сайте мастерской "Рыжая Соня" получен новый заказ.
Имя заказчика: '.$order['familia'].' '.$order['name']);
	    		$mail->setFrom($order['email'], $order['name']);
	    		$mail->addTo($to);
	    		$mail->setSubject('Новый заказ на сайте "Рыжая Соня"');
	    		$mail->send();
	    		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Спасибо. Ваша заявка отправлена. Мы обязательно с Вами свяжемся в течение 48 часов :)');
				$this->_redirect('/shop/form/success');
			}
		}
	}

  
  public function successAction()
  {
    if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
      $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
    } else {
      $this->_redirect('/');    
    } 
  }
}