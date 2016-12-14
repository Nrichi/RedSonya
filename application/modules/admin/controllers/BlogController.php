<?php
class Admin_BlogController extends Zend_Controller_Action
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
			//вывод списка всех постов блога
			$q = Doctrine_Query::create()
				->select('b.*, i.img as img, i.description as desc')
				->from('Redsonya_Model_Blog b')
				->leftJoin('b.Redsonya_Model_BlogImg i')
				->where('b.post_id = i.post_id')
				->orderBy('b.created DESC, i.img_id ASC');
		
				$perPage = 10;
				$numPageLinks = 5;
		
				//инициализация компонента разбиения на страницы
				$pager = new Doctrine_Pager($q, $input->page, $perPage);
		
				//выполняем запрос учитывающий номер страницы
				$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
		
				//инициализация макета компонанта для разбиения на страницы
				$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
				$pagerUrlBase = $this->view->url(array(), 'admin-blog-index', 1) . "/{%page}";
				$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
		
				//устанавливаем шаблон для отображения ссылки на страницу
				$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
				$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
				$pagerLayout->setSeparatorTemplate('&nbsp;');
		
				//присваиваем значения переменным представления
				$this->view->posts = $result;
				$this->view->pages = $pagerLayout->display(null, true);
			} else {
				throw new Zend_Controller_Action_Exception('Страница не найдена');
			}
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_BlogPost();
		$this->view->form = $form;
		
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
		
				$input = $form->getValues();				//получаем данные из формы
				$item = new Redsonya_Model_Blog();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
				//$item->post_id = NULL;
				$item->created = time();
				$item->save();								//сохраняем данные в базе в таблице Articles и получаем идентификатор вставленной записи
				$id = $item->post_id;
		
				//создаем директорию с именем соответствующем идентификатору добавленной записи для добавления картинок к посту в блоге
				mkdir(realpath(dirname('.')).DIRECTORY_SEPARATOR.'blogfotos'.DIRECTORY_SEPARATOR.$id);
		
				$postimg = new Redsonya_Model_BlogImg();	//добавляем фото с описанием в таблицу BlogImg
				$postimg->fromArray($input);
				$postimg->post_id = $id;
		
				$file = $form->img->getFileInfo();			//если передана картинка, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'blog'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
		
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'blogfotos'.
						DIRECTORY_SEPARATOR.
						$id.
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение
		
				$postimg->img = $newName;
				$postimg->save();							//сохраняем строку в базе в таблице ArticlesImg
		
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новый пост был успешно создан в блоге.
						Теперь вы можете добавить к нему фотографии с описанием, перейдя в режим просмотра.');
				$this->_redirect('/admin/blog/index');
			}
		}
	}
	
	public function displayAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
	
		//отображаем пост в виде таблицы, каждая строка которой представляет собой текстовый блок с картинкой,
		//каждый этот блок можно редактировать либо удалить если он только не единственный, иначе удалить нельзя
		$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id' => array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		if ($input->isValid()) {
			//получаем информацию о посте
			$post = Doctrine::getTable('Redsonya_Model_Blog')->find($input->id);
			$this->view->post = $post;
				
			//получаем все блоки с картинками
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_BlogImg i')
				->where('i.post_id = ?', $input->id)
				->orderBy('i.img_id ASC');
			$result = $q->fetchArray();
			$this->view->blocks = $result;
		} else {
			throw new Zend_Controller_Action_Exception('Пост не найден', 404);
		}

		$form = new Redsonya_Form_BlogAddBlock($input->id);
		$this->view->form = $form;

		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
				$input = $form->getValues();					//получаем данные из формы
				$item = new Redsonya_Model_BlogImg();
				$item->fromArray($input);						//заполняем строку новыми данными из формы

				$file = $form->img->getFileInfo();				//если передана, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'blog'.time().'.'.$ext;				//генерируем уникальное имя для загруженной картинки

				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'blogfotos'.
						DIRECTORY_SEPARATOR.
						$item['post_id'].
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку articles

				$item->img_id = NULL;
				$item->img = $newName;
				$item->save();								//сохраняем строку в базе

				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая фотография с описанием была успешно добавлена к посту в блоге.');
				$this->_redirect('/admin/blog/display/'.$item['post_id']);
			}
		}
	}

	public function editblockAction()
	{
		$form = new Redsonya_Form_BlogEditBlock();
		$this->view->form = $form;
	
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
	
				$input = $form->getValues();				//получаем данные из формы
				$item = Doctrine::getTable('Redsonya_Model_BlogImg')->find($input['img_id']);	//находим строку по id-шнику, которую нужно обновить
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
				$item->fromArray($input);					//заполняем строку новыми данными из формы
	
				if ($item->img == '')						//если картинка не была передана, оставляем старую
				{
					$imgName = $oldImgName;
				}
				else
				{
					$file = $form->img->getFileInfo();		//если фотка передана, получаем о ней данные и ее расширение
					$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
	
					$newName = 'blog'.time().'.'.$ext;		//генерируем уникальное имя для загруженной картинки
	
					$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
							DIRECTORY_SEPARATOR.
							'blogfotos'.
							DIRECTORY_SEPARATOR.
							$item['post_id'].
							DIRECTORY_SEPARATOR.
							$newName);
					$form->img->receive();					//получаем само изображение и помещаем в папку blogfotos
					$imgName = $newName;					//задаем ей новое имя
	
					unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
					DIRECTORY_SEPARATOR.
					'blogfotos'.
					DIRECTORY_SEPARATOR.
					$item['post_id'].
					DIRECTORY_SEPARATOR.
					$oldImgName);
				}
				$item->img = $imgName;
				$item->save();								//обновляем строку в базе
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Блок с фотографией и описанием был успешно обновлен.');
				$this->_redirect('/admin/blog/display/'.$input['post_id']);
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
					->from('Redsonya_Model_BlogImg i')
					->where('i.img_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->form->populate($result[0]);
					$this->view->block = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Пост не найден', 404);
				}
			} else {
				throw new Zend_Controller_Action_Exception('Пост не найден', 404);
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
	
				//находим в базе фотку блока поста
				$item = Doctrine::getTable('Redsonya_Model_BlogImg')->find($del);
				$postid = $item['post_id'];
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
	
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_BlogImg i')
					->where('i.img_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографии из поста. Запись не найдена в базе данных');
	
				//удаляем фотку
				unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
				DIRECTORY_SEPARATOR.
				'blogfotos'.
				DIRECTORY_SEPARATOR.
				$postid.
				DIRECTORY_SEPARATOR.
				$oldImgName);
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Фотография и описание к ней были успешно удалены из поста.');
				$this->_redirect('/admin/blog/display/'.$postid);
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
					->from('Redsonya_Model_BlogImg i')
					->where('i.img_id = ?', $blockid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->block = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Блок фотографии с описанием не найден', 404);
				}
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
	
				//находим имена всех фоток в данном посте из таблицы BlogImg
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_BlogImg i')
					->where('i.post_id = ?', $del);
				$images = $q->fetchArray();
	
				//удаляем их из директории
				foreach ($images as $img) {
					unlink(realpath(dirname('.')).
					DIRECTORY_SEPARATOR.
					'blogfotos'.
					DIRECTORY_SEPARATOR.
					$del.
					DIRECTORY_SEPARATOR.
					$img['img']);
				}
	
				//удаляем директорию с именем идентификатора класса
				rmdir(realpath(dirname('.')).DIRECTORY_SEPARATOR.'blogfotos'.DIRECTORY_SEPARATOR.$del);
	
				//удаляем все записи из таблицы фоток BlogImg
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_BlogImg i')
					->where('i.post_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографий поста из базы данных.');
	
				//удаляем все комментарии к данному посту
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_BlogComments c')
					->where('c.post_id = ?', $del);
				$result = $q->execute();
				
				//удаляем запись о самом посте с таблицы Blog
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_Blog b')
					->where('b.post_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении поста из базы данных.');

				//вывод сообщения об удалении и редирект на список статей
				$this->_helper->getHelper('FlashMessenger')->addMessage('Пост был успешно удален из Блога.');
				$this->_redirect('/admin/blog/index');
			}
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
	
			//если передан правильный идентификатор поста, находим его название и передаем результат в вид для подтверждения его удаления
			if ($input->isValid()) {
				$postid = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Blog b')
					->where('b.post_id = ?', $postid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->post = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Пост не найдена', 404);
				}
			}
		}
	}
	
	public function commentsAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
		
		//фильтруем идентификатор поста
		$filters = array('id'	=> array('HtmlEntities', 'StripTags', 'StringTrim'),
						 'page'	=> array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id'	=> array('NotEmpty', 'Int'),
							'page'	=> array('Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		
		//получаем комментарии к данному посту
		if ($input->isValid()) {
			$postid = $input->id;
			
			//получаем пост, к которому относятся комментарии, для вывода названия в заголовке
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_Blog b')
				->where('b.post_id = ?', $postid);
			$result = $q->fetchOne();
			$this->view->post = $result;
			
			//получаем комментарии
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_BlogComments c')
				->where('c.post_id = ?', $postid)
				->orderBy('c.created DESC');
			
			$perPage = 3;
			$numPageLinks = 5;
			
			//инициализация компонента разбиения на страницы
			$pager = new Doctrine_Pager($q, $input->page, $perPage);
			
			//выполняем запрос учитывающий номер страницы
			$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
			
			//инициализация макета компонанта для разбиения на страницы
			$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
			$pagerUrlBase = $this->view->url(array('id' => $postid), 'admin-blog-comments', 1) . "/{%page}";
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
				//находим id-шник поста к которому относится удаляемый комментарий для редиректа
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_BlogComments c')
					->where('c.comment_id = ?', $del);
				$result = $q->fetchArray();
				$post_id = $result[0]['post_id'];

				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_BlogComments c')
					->where('c.comment_id = ?', $del);
				$result = $q->execute();

				$this->_helper->getHelper('FlashMessenger')->addMessage('Комментарий был успешно удален');
				$this->_redirect('/admin/blog/comments/'.$post_id);
			}
			else {
				throw new Zend_Controller_Action_Exception('Неверный ввод');
			}
		}
		else
		{
			//для подтверждения удаления комментария к посту
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
		
			//если передан правильный идентификатор комментария, находим его и передаем результат в вид
			if ($input->isValid()) {
				$comm_id = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_BlogComments с')
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
}