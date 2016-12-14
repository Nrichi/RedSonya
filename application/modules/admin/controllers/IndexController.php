<?php
class Admin_IndexController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->setLayout('adminlogin');          
	}

	// действие для входа
	public function indexAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
			
		$form = new Redsonya_Form_Login();
		$this->view->form = $form;

		if ($this->getRequest()->isPost()) {
			if ($form->isValid($this->getRequest()->getPost())) {
				$values = $form->getValues();
				$adapter = new Redsonya_Auth_Adapter_Doctrine($values['username'], $values['password']);
				$auth = Zend_Auth::getInstance();
				$result = $auth->authenticate($adapter);
				if ($result->isValid()) {
					$session = new Zend_Session_Namespace('redsonya.auth');
					$session->user = $adapter->getResultArray('Password');
					if (isset($session->requestURL)) {
						$url = $session->requestURL;
						unset($session->requestURL);
						$this->_redirect($url);
					} else {
						$this->_helper->getHelper('FlashMessenger')
							->addMessage('Вы успешно вошли в административную панель');
						$this->_redirect('/admin/mainpage/index');
					}
				} else {
					$this->view->message = 'Ошибка входа в админ-панель. Повторите попытку позже';
				}
			}
		}
	}
	
	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		Zend_Session::destroy();
		$this->_redirect('/admin');
	}
	
	public function forgotpassAction()
	{
		//отображаем форму для ввода логина
		$form = new Redsonya_Form_ForgotPass();
		$this->view->form = $form;
		
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$username = $input['username'];
				//гененерируем новый пароль
				$newpass = $this->generatePass();
				
				//обновляем запись в базе данный, сохраняем новый пароль
				$q = Doctrine_Query::create()
					->update('Redsonya_Model_User u')
					->set('u.password', '?', $newpass)
					->where('u.username = ?', $username)
					->execute();
				
				//отправляем новый пароль на е-мейл админа
				$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
				$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
				$to = $localConfig->global->defaultEmailAddress;
				$mail = new Zend_Mail();
				$mail->setBodyText('Смена пароля для входа в админ-панель на сайте мастерской "Рыжая Соня". <br />
		        					Ваш новый пароль '.$newpass);
				$mail->setFrom('robot@mysite.ru');
				$mail->addTo($to);
				$mail->setSubject('Смена пароля администратора на сайте "Рыжая Соня"');
				//$mail->send();
				 
				$this->_helper->getHelper('FlashMessenger')->addMessage('На ваш e-mail был отправлен новый пароль для входа в админ-панель');
				$this->_redirect('/admin');
			} else {
				$this->_helper->getHelper('FlashMessenger')->addMessage('Неверный логин или пароль');
				$this->_redirect('/admin');
			}
		}
	}
	
	private function generatePass()
	{
		$chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";	//допустимые символы
		$max=10;	// Количество символов в пароле
		$size=StrLen($chars)-1;	// Определяем количество символов в $chars 
		$password=null;	// Определяем пустую переменную, в которую и будем записывать символы.
		while($max--)
			$password.=$chars[rand(0,$size)];
		return $password;
	}
}