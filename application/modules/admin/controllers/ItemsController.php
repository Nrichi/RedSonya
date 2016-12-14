<?php
class Admin_ItemsController extends Zend_Controller_Action
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
				//вывод списка всех товаров
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Items i')
					->orderBy('i.created DESC');

				$perPage = 10;
				$numPageLinks = 5;
		
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
		
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
		
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-items-index', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
		
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
		
				//присваиваем значения переменным представления
				$this->view->items = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_Item();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
				//получаем данные из формы и заполняем модель Items
				$input = $form->getValues();				//получаем данные из формы
				$input['width'] = floatval(str_replace(',', '.', $input['width']));
				$input['height'] = floatval(str_replace(',', '.', $input['height']));
				$input['depth'] = floatval(str_replace(',', '.', $input['depth']));
				$input['weight'] = floatval(str_replace(',', '.', $input['weight']));
				$item = new Redsonya_Model_Items();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				$file = $form->img->getFileInfo();			//если передана, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'item'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'foto'.
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку foto
		
				//заполняем модель
				//$item->item_id = NULL;
				$item->created = time();
				$item->img = $newName;
				$item->reserve = 0;
				$item->save();								//сохраняем строку в базе

				//получаем награды и сохраняем в таблицу ItemAward в базе данных
				if($input['award1'])
				{
					$itemawd = new Redsonya_Model_ItemAward();
					//$itemawd->id = null;
					$itemawd->item_id = $item->item_id;	//идентификатор только что вставленной записи в Items
					$itemawd->award_id = $input['award1'];
					$itemawd->save();
				}
				
				if($input['award2'])
				{
					$itemawd = new Redsonya_Model_ItemAward();
					//$itemawd->id = null;
					$itemawd->item_id = $item->item_id;	//идентификатор только что вставленной записи в Items
					$itemawd->award_id = $input['award2'];
					$itemawd->save();
				}
				
				if($input['award3'])
				{
					$itemawd = new Redsonya_Model_ItemAward();
					//$itemawd->id = null;
					$itemawd->item_id = $item->item_id;	//идентификатор только что вставленной записи в Items
					$itemawd->award_id = $input['award3'];
					$itemawd->save();
				}

				$this->_helper->getHelper('FlashMessenger')->addMessage('Новый товар был успешно добавлен в каталог.');
				$this->_redirect('/admin/items/index');
			}
		}
	}

	public function updateAction()
	{
		$form = new Redsonya_Form_ItemUpdate();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$input['width'] = floatval(str_replace(',', '.', $input['width']));
				$input['height'] = floatval(str_replace(',', '.', $input['height']));
				$input['depth'] = floatval(str_replace(',', '.', $input['depth']));
				$input['weight'] = floatval(str_replace(',', '.', $input['weight']));
				$item = Doctrine::getTable('Redsonya_Model_Items')->find($input['item_id']);	//находим строку по id-шнику, которую нужно обновить
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
				$created = $item['created'];
				$reserve = $item['reserve'];
				$item->fromArray($input);					//заполняем строку новыми данными из формы
		
				if ($item->img == '')						//если картинка не была передана, оставляем старую
				{
					$imgName = $oldImgName;
				}
				else
				{
					$file = $form->img->getFileInfo();		//если передана, получаем о ней данные и ее расширение
					$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
		
					$newName = 'item'.time().'.'.$ext;		//генерируем уникальное имя для загруженной картинки
		
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
		
				$item->created = $created;
				$item->img = $imgName;
				$item->reserve = $reserve;
		
				$item->save();								//обновляем строку в базе
		
				//обновляем таблицу с наградами
				//если таблица ItemAward уже содержит награды для данного товара, 
				//удаляем их и вставляем новые из формы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ItemAward w')
					->where('w.item_id = ?', $item->item_id);
				$result = $q->execute(); 
				//if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при обновлении наград (номинаций) в базе данных.');
				
				if($input['award1'])
				{
					$itemawd = new Redsonya_Model_ItemAward();
					$itemawd->item_id = $item->item_id;		//идентификатор товара
					$itemawd->award_id = $input['award1'];
					$itemawd->save();
				}
				
				if($input['award2'])
				{
					$itemawd = new Redsonya_Model_ItemAward();
					$itemawd->item_id = $item->item_id;
					$itemawd->award_id = $input['award2'];
					$itemawd->save();
				}
				
				if($input['award3'])
				{
					$itemawd = new Redsonya_Model_ItemAward();
					$itemawd->item_id = $item->item_id;
					$itemawd->award_id = $input['award3'];
					$itemawd->save();
				}
				
				$this->_helper->getHelper('FlashMessenger')->addMessage('Информация о товаре была успешно обновлена.');
				$this->_redirect('/admin/items/index');
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
					->from('Redsonya_Model_Items i')
					->where('i.item_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$item = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
				
				//получаем награды
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ItemAward w')
					->where('w.item_id = ?', $input->id);
				$result = $q->fetchArray();
				
				//добавляем их в массив для заполнения формы
				if (isset($result[0])) $item['award1'] = $result[0]['award_id'];
				if (isset($result[1])) $item['award2'] = $result[1]['award_id'];
				if (isset($result[2])) $item['award3'] = $result[2]['award_id'];
				
				$this->view->form->populate($item);
				
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
		
				//находим в базе фотку статьи
				$item = Doctrine::getTable('Redsonya_Model_Items')->find($del);
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
		
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_Items i')
					->where('i.item_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении товара из базы данных. Товар не найден.');
		
				//удаляем все комментарии к данному товару
				 $q = Doctrine_Query::create()
					->delete('Redsonya_Model_ItemComments c')
					->where('c.item_id = ?', $del);
				$result = $q->execute();
		
				//удаляем фотку
				unlink(realpath(dirname('.')).DIRECTORY_SEPARATOR.'foto'.DIRECTORY_SEPARATOR.$oldImgName);
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Товар был успешно удален.');
				$this->_redirect('/admin/items/index');
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
		
			//если передан правильный идентификатор статьи, находим ее название и передаем результат в вид
			if ($input->isValid()) {
				$itemid = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Items i')
					->where('i.item_id = ?', $itemid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->item = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
	
	public function commentsAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
		
			//фильтруем идентификатор товара
			$filters = array('id'	=> array('HtmlEntities', 'StripTags', 'StringTrim'),
							 'page'	=> array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id'	=> array('NotEmpty', 'Int'),
								'page'	=> array('Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			//получаем комментарии к данному товару
			if ($input->isValid()) {
				$itemid = $input->id;
					
				//получаем товар, к которому относятся комментарии, для вывода названия в заголовке
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Items i')
					->where('i.item_id = ?', $itemid);
				$result = $q->fetchOne();
				$this->view->item = $result;
					
				//получаем комментарии
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ItemComments c')
					->where('c.item_id = ?', $itemid)
					->orderBy('c.created DESC');
					
				$perPage = 3;
				$numPageLinks = 5;
					
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
					
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
					
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array('id' => $itemid), 'admin-items-comments', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
					
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
		
				$this->view->comments = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			}
	}
	
	public function commdelAction()
	{
		if ($this->getRequest()->isPost()) {
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			if ($input->isValid()) {
		
				$del = $this->getRequest()->getPost('id');
				//находим id-шник товара к которому относится удаляемый комментарий для редиректа
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ItemComments c')
					->where('c.comment_id = ?', $del);
				$result = $q->fetchArray();
				$itemid = $result[0]['item_id'];
		
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ItemComments c')
					->where('c.comment_id = ?', $del);
				$result = $q->execute();
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Комментарий был успешно удален');
				$this->_redirect('/admin/items/comments/'.$itemid);
			}
			else {
				throw new Zend_Controller_Action_Exception('Неверный ввод');
			}
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			//если передан правильный идентификатор комментария, находим его и передаем результат в вид
			if ($input->isValid()) {
				$comm_id = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ItemComments с')
					->where('с.comment_id = ?', $comm_id);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->comment = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
	
	public function imagesAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
		
		//фильтруем идентификатор товара
		$filters = array('id'	=> array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id'	=> array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		
		//получаем id-шник товара из GET
		if ($input->isValid()) {
			$itemid = $input->id;
					
			//получаем товар для вывода названия в заголовке
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_Items i')
				->where('i.item_id = ?', $itemid);
			$result = $q->fetchOne();
			$this->view->item = $result;
					
			//получаем фотки
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_ItemImg i')
				->where('i.item_id = ?', $itemid);
			$result = $q->fetchArray();
			$this->view->pics = $result;
		}
	}
	
	public function imgdelAction()
	{
	if ($this->getRequest()->isPost()) {
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			if ($input->isValid()) {
		
				$del = $this->getRequest()->getPost('id');
				
				//находим id-шник товара к которому относится удаляемоя фотка для редиректа и имя фотки для удаления
				$pic = Doctrine::getTable('Redsonya_Model_ItemImg')->find($del);
				$oldImgName = $pic['img'];
				$itemid = $pic['item_id'];
				
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ItemImg i')
					->where('i.img_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографии товара из базы данных. Картинка не найдена.');
				
				//удаляем саму фотку из файловой системы
				unlink(realpath(dirname('.')).DIRECTORY_SEPARATOR.'foto'.DIRECTORY_SEPARATOR.$oldImgName);

				$this->_helper->getHelper('FlashMessenger')->addMessage('Фотография была успешно удалена.');
				$this->_redirect('/admin/items/images/'.$itemid);
			}
			else {
				throw new Zend_Controller_Action_Exception('Неверный ввод');
			}
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			//если передан правильный идентификатор фотки, находим его и передаем результат в вид
			if ($input->isValid()) {
				$pic_id = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ItemImg i')
					->where('i.img_id = ?', $pic_id);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->pic = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
				}
			}
		}
	}
	
	public function addfotoAction()
	{
		//фильтруем GET запрос
		$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id' => array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		
		//если передан правильный идентификатор товара, передаем его в форму и отображаем ее
		if ($input->isValid()) {
			$itemid = $input->id;
		
			$form = new Redsonya_Form_AddItemImg($itemid);
			$this->view->form = $form;
		} else {
			throw new Zend_Controller_Action_Exception('Неверный идентификатор товара', 404);
		}
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$itemid = $this->getRequest()->getPost('item_id');
				$item = new Redsonya_Model_ItemImg();
				$item->fromArray($input);					//заполняем строку новыми данными из формы

				$file = $form->img->getFileInfo();
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'itempic'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'foto'.
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку foto
		
				//заполняем модель
				$item->img_id = NULL;
				$item->img = $newName;
				$item->save();								//сохраняем строку в базе
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая фотография была успешно добавлена.');
				$this->_redirect('/admin/items/images/'.$itemid);
			}
		}
	}
}