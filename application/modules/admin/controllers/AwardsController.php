<?php
class Admin_AwardsController extends Zend_Controller_Action
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
				//вывод списка всех статей
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Awards a')
					->orderBy('a.award_id DESC');
				$perPage = 10;
				$numPageLinks = 5;
		
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
		
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
		
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-awards-index', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
		
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
		
				//присваиваем значения переменным представления
				$this->view->awards = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_Award();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$item = new Redsonya_Model_Awards();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				$file = $form->img->getFileInfo();			//если передана, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'awd'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'itemawards'.
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку foto
		
				$item->award_id = NULL;
				$item->img = $newName;
				$item->save();								//сохраняем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая награда была успешно добавлена.');
				$this->_redirect('/admin/awards/index');
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
		
				//находим в базе фотку статьи
				$item = Doctrine::getTable('Redsonya_Model_Awards')->find($del);
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
		
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_Awards a')
					->where('a.award_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении награды из базы данных. Награда не найдена');
		
				//удаляем фотку
				unlink(realpath(dirname('.')).DIRECTORY_SEPARATOR.'itemawards'.DIRECTORY_SEPARATOR.$oldImgName);
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Награда была успешно удалена.');
				$this->_redirect('/admin/awards/index');
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
		
			//если передан правильный идентификатор награды, находим ее и передаем результат в вид
			if ($input->isValid()) {
				$awd = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Awards a')
					->where('a.award_id = ?', $awd);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->award = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}