<?php
class Admin_MclassesController extends Zend_Controller_Action
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
				$pagerUrlBase = $this->view->url(array(), 'admin-mclasses-index', 1) . "/{%page}";
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
		
		//отображаем мастер-класс в виде таблицы, каждая строка которой представляет собой текстовый блок с картинкой,
		//каждый этот блок можно редактировать либо удалить
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
		
		$form = new Redsonya_Form_McAddBlock($input->id);
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
				//$mcid = $this->getRequest()->getPost('mc_id');
				$input = $form->getValues();				//получаем данные из формы
				$item = new Redsonya_Model_VirtualmcImg();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				$file = $form->img->getFileInfo();			//если передана, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'mc'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
					DIRECTORY_SEPARATOR.
					'mclasses'.
					DIRECTORY_SEPARATOR.
					$item['mc_id'].
					DIRECTORY_SEPARATOR.
					$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку mclasses
		
				$item->img_id = NULL;
				$item->img = $newName;
				$item->save();								//сохраняем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая фотография с описанием была успешно добавлена.');
				$this->_redirect('/admin/mclasses/display/'.$item['mc_id']);
			}
		}
	}
	
	public function editblockAction()
	{
		$form = new Redsonya_Form_McBlock();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$item = Doctrine::getTable('Redsonya_Model_VirtualmcImg')->find($input['img_id']);	//находим строку по id-шнику, которую нужно обновить
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
		
					$newName = 'mc'.time().'.'.$ext;		//генерируем уникальное имя для загруженной картинки
		
					$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
							DIRECTORY_SEPARATOR.
							'mclasses'.
							DIRECTORY_SEPARATOR.
							$item['mc_id'].
							DIRECTORY_SEPARATOR.
							$newName);
					$form->img->receive();					//получаем само изображение и помещаем в папку mclasses
					$imgName = $newName;					//задаем ей новое имя
		
					unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
						DIRECTORY_SEPARATOR.
						'mclasses'.
						DIRECTORY_SEPARATOR.
						$item['mc_id'].
						DIRECTORY_SEPARATOR.
						$oldImgName);
				}
				$item->img = $imgName;
				$item->save();								//обновляем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Блок с фотографией и описанием был успешно обновлен.');
				$this->_redirect('/admin/mclasses/display/'.$input['mc_id']);
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
					->from('Redsonya_Model_VirtualmcImg i')
					->where('i.img_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->form->populate($result[0]);
					$this->view->block = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			} else {
				throw new Zend_Controller_Action_Exception('Страница не существует');
			}
		}
	}
	
	public function delblockAction()
	{
		if ($this->getRequest()->isPost()) {
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			if ($input->isValid()) {
		
				$del = $this->getRequest()->getPost('id');
		
				//находим в базе фотку статьи
				$item = Doctrine::getTable('Redsonya_Model_VirtualmcImg')->find($del);
				$mcid = $item['mc_id'];
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
		
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_VirtualmcImg i')
					->where('i.img_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографии из мастеркласса. Запись не найдена в базе данных');

				//удаляем фотку
				unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
					DIRECTORY_SEPARATOR.
					'mclasses'.
					DIRECTORY_SEPARATOR.
					$mcid.
					DIRECTORY_SEPARATOR.
					$oldImgName);
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Фотография и описание к ней были успешно удалены из мастер-класса.');
				$this->_redirect('/admin/mclasses/display/'.$mcid);
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
		
			//если передан правильный идентификатор фотки, находим ее и передаем результат в вид
			if ($input->isValid()) {
				$blockid = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_VirtualmcImg i')
					->where('i.img_id = ?', $blockid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->block = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_McCreate();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$item = new Redsonya_Model_Virtualmc();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
				//$item->mc_id = NULL;
				$item->created = time();
				$item->save();								//сохраняем данные в базе в таблице Virtualmc и получаем идентификатор вставленной записи
				$id = $item->mc_id;
				
				//создаем директорию с именем соответствующем идентификатору добавленной записи
				mkdir(realpath(dirname('.')).DIRECTORY_SEPARATOR.'mclasses'.DIRECTORY_SEPARATOR.$id);
				
				$mcimg = new Redsonya_Model_VirtualmcImg();//добавляем фото с описанием в таблицу VirtualmcImg
				$mcimg->fromArray($input);
				$mcimg->mc_id = $id;
				
				$file = $form->img->getFileInfo();			//если передана картинка, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'mc'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
					DIRECTORY_SEPARATOR.
					'mclasses'.
					DIRECTORY_SEPARATOR.
					$id.
					DIRECTORY_SEPARATOR.
					$newName);
				$form->img->receive();						//получаем само изображение

				$mcimg->img = $newName;
				$mcimg->save();								//сохраняем строку в базе в таблице VirtualmcImg
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новый виртуальный мастер-класс был успешно создан. 
						Теперь вы можете добавить в него фотографии с описанием, перейдя в режим просмотра.');
				$this->_redirect('/admin/mclasses/index');
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
		
				//находим имена всех фоток в данном мк из таблицы VirtualmcImg
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_VirtualmcImg i')
					->where('i.mc_id = ?', $del);
				$images = $q->fetchArray(); 
				
				//удаляем их из директории
				foreach ($images as $img) {
					unlink(realpath(dirname('.')).DIRECTORY_SEPARATOR.'mclasses'.DIRECTORY_SEPARATOR.$del.DIRECTORY_SEPARATOR.$img['img']);
				}
				
				//удаляем директорию с именем идентификатора класса
				rmdir(realpath(dirname('.')).DIRECTORY_SEPARATOR.'mclasses'.DIRECTORY_SEPARATOR.$del);

				//удаляем все записи из таблицы фоток VirtualmcImg
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_VirtualmcImg i')
					->where('i.mc_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографий мастер-класса из базы данных.');
				
				//удаляем запись о самом мк с таблицы Virtualmc
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_Virtualmc m')
					->where('m.mc_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении записи о мастер-классе из базы данных.');
				
				//вывод сообщения об удалении и редирект на список мк
				$this->_helper->getHelper('FlashMessenger')->addMessage('Виртуальный мастер-класс был успешно удален.');
				$this->_redirect('/admin/mclasses/index');
			}		
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			//если передан правильный идентификатор мастер-класса, находим его название и передаем результат в вид для подтверждения удаления
			if ($input->isValid()) {
				$mcid = $input->id;
				$q = Doctrine_Query::create()
				->from('Redsonya_Model_Virtualmc m')
				->where('m.mc_id = ?', $mcid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->mclass = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
}