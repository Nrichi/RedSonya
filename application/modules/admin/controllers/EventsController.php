<?php
class Admin_EventsController extends Zend_Controller_Action
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
			
		//вывод списка всех мастеров
		$q = Doctrine_Query::create()
			->from('Redsonya_Model_AboutPosts p');
		$result = $q->fetchArray();
		$this->view->posts = $result;
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_Event();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$item = new Redsonya_Model_AboutPosts();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
				
				$file = $form->img->getFileInfo();			//если передана, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'about'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'foto'.
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку fot
				
				$item->post_id = NULL;						//айдишник автоинкремент, поэтому указываем его как null
				$item->img = $newName;
				$item->save();								//сохраняем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новый блок был успешно добавлен.');
				$this->_redirect('/admin/events/index');
			}
		}
	}
	
	public function updateAction()
	{
		$form = new Redsonya_Form_EventUpdate();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {		
		
				$input = $form->getValues();				//получаем данные из формы
				$item = Doctrine::getTable('Redsonya_Model_AboutPosts')->find($input['post_id']);	//находим строку по id-шнику, которую нужно обновить
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				if ($item->img == '')						//если картинка не была передана, оставляем старую
				{
					$imgName = $oldImgName;
				}
				else
				{
					$file = $form->img->getFileInfo();		//если передана, получаем о ней данные и ее расширение
					$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
		
					$newName = 'about'.time().'.'.$ext;		//генерируем уникальное имя для загруженной картинки
		
					$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
							DIRECTORY_SEPARATOR.
							'foto'.
							DIRECTORY_SEPARATOR.
							$newName);
					$form->img->receive();					//получаем само изображение и помещаем в папку foto
					$imgName = $newName;					//задаем ей новое имя
		
					unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
					DIRECTORY_SEPARATOR.
					'foto'.
					DIRECTORY_SEPARATOR.
					$oldImgName);
				}
		
				$item->img = $imgName;
		
				$item->save();								//обновляем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Текстовый блок был успешно обновлен');
				$this->_redirect('/admin/events/index');
			}
		} else {
			// if GET request
			// set filters and validators for GET input
			// test if input is valid
			// retrieve requested record
			// pre-populate form
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
			if ($input->isValid()) {
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_AboutPosts p')
				->where('p.post_id = ?', $input->id);
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
			$del = $this->getRequest()->getPost('id');

			//находим в базе фотку мастера
			$item = Doctrine::getTable('Redsonya_Model_AboutPosts')->find($del);
			$oldImgName = $item['img'];					//сохраняем имя старой фотки
				
			//удаляем запись из базы
			$q = Doctrine_Query::create()
            	->delete('Redsonya_Model_AboutPosts p')
            	->where('p.post_id = ?', $del);		
			$result = $q->execute();

			unlink(realpath(dirname('.')).DIRECTORY_SEPARATOR.'foto'.DIRECTORY_SEPARATOR.$oldImgName);
		
			$this->_helper->getHelper('FlashMessenger')->addMessage('Блок был успешно удален');
			$this->_redirect('/admin/events/index');
		} 
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
			 
			//если передан правильный идентификатор мастера, находим его имя и передаем результат в вид
			if ($input->isValid()) {
				$postid = $input->id;
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_AboutPosts p')
				->where('p.post_id = ?', $postid);
			
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->post = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}