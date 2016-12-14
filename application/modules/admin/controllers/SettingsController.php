<?php
class Admin_SettingsController extends Zend_Controller_Action
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
	
public function changepassAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
			
		//отображаем форму для смены пароля
		$form = new Redsonya_Form_ChangePass();
		$this->view->form = $form;
		
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				
				$item = Doctrine::getTable('Redsonya_Model_User')->find($input['username']);
				if(!$item) throw new Zend_Controller_Action_Exception('Неверный логин');
				if(($item['password'] === $input['oldpass']) && ($input['password1'] === $input['password2'])){
					$item->password = $input['password1'];
					$item->save();
				} else {
					throw new Zend_Controller_Action_Exception('Введенные пароли не совпадают');
				}
				
				$this->_helper->getHelper('FlashMessenger')->addMessage('Пароль был успешно изменен');
				$this->_redirect('/admin/settings/success');
			}				
		}
	}
	
	public function successAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper
			->getHelper('FlashMessenger')
			->getMessages();
		} else {
			$this->_redirect('/admin/mainpage/index');
		}
	}
}