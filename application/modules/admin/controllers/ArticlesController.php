<?php
class Admin_ArticlesController extends Zend_Controller_Action
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
				->select('a.*, i.img as img, i.description as desc')
				->from('Redsonya_Model_Articles a')
				->leftJoin('a.Redsonya_Model_ArticlesImg i')
				->where('a.article_id = i.article_id')
				//->groupBy('o.status')
				->orderBy('a.created DESC, i.img_id ASC');
		
			$perPage = 10;
			$numPageLinks = 5;
		
			//инициализация компонента разбиения на страницы
			$pager = new Doctrine_Pager($q, $input->page, $perPage);
		
			//выполняем запрос учитывающий номер страницы
			$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
		
			//инициализация макета компонанта для разбиения на страницы
			$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
			$pagerUrlBase = $this->view->url(array(), 'admin-articles-index', 1) . "/{%page}";
			$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
		
			//устанавливаем шаблон для отображения ссылки на страницу
			$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
			$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
			$pagerLayout->setSeparatorTemplate('&nbsp;');
		
			//присваиваем значения переменным представления
			$this->view->articles = $result;
			$this->view->pages = $pagerLayout->display(null, true);
		} else {
			throw new Zend_Controller_Action_Exception('Страница не найдена');
		}
	}
	
	public function createAction()
	{
		$form = new Redsonya_Form_Article();
		$this->view->form = $form;
	
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
	
				$input = $form->getValues();				//получаем данные из формы
				$item = new Redsonya_Model_Articles();
				$item->fromArray($input);					//заполняем строку новыми данными из формы
				//$item->mc_id = NULL;
				$item->created = time();
				$item->save();								//сохраняем данные в базе в таблице Articles и получаем идентификатор вставленной записи
				$id = $item->article_id;
	
				//создаем директорию с именем соответствующем идентификатору добавленной записи для добавления картинок к статье
				mkdir(realpath(dirname('.')).DIRECTORY_SEPARATOR.'allarticles'.DIRECTORY_SEPARATOR.$id);
	
				$artimg = new Redsonya_Model_ArticlesImg();//добавляем фото с описанием в таблицу ArticlesImg
				$artimg->fromArray($input);
				$artimg->article_id = $id;
	
				$file = $form->img->getFileInfo();			//если передана картинка, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'art'.time().'.'.$ext;			//генерируем уникальное имя для загруженной картинки
	
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
						DIRECTORY_SEPARATOR.
						'allarticles'.
						DIRECTORY_SEPARATOR.
						$id.
						DIRECTORY_SEPARATOR.
						$newName);
				$form->img->receive();						//получаем само изображение
	
				$artimg->img = $newName;
				$artimg->save();							//сохраняем строку в базе в таблице ArticlesImg
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая статья была успешно создана.
						Теперь вы можете добавить к ней фотографии с описанием, перейдя в режим просмотра.');
				$this->_redirect('/admin/articles/index');
			}
		}
	}
	
	public function displayAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
	
		//отображаем статью в виде таблицы, каждая строка которой представляет собой текстовый блок с картинкой,
		//каждый этот блок можно редактировать либо удалить
		$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id' => array('NotEmpty', 'Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());
		if ($input->isValid()) {
			//получаем информацию о статье
			$article = Doctrine::getTable('Redsonya_Model_Articles')->find($input->id);
			$this->view->article = $article;
					
			//получаем все блоки с картинками
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_ArticlesImg i')
				->where('i.article_id = ?', $input->id)
				->orderBy('i.img_id ASC');
			$result = $q->fetchArray();
			$this->view->blocks = $result;
		} else {
			throw new Zend_Controller_Action_Exception('Страница не существует', 404);
		}
	
		$form = new Redsonya_Form_ArticleAddBlock($input->id);
		$this->view->form = $form;
	
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
				$input = $form->getValues();					//получаем данные из формы
				$item = new Redsonya_Model_ArticlesImg();
				$item->fromArray($input);						//заполняем строку новыми данными из формы
	
				$file = $form->img->getFileInfo();				//если передана, получаем о ней данные и ее расширение
				$ext = pathinfo($file['img']['name'], PATHINFO_EXTENSION);
				$newName = 'art'.time().'.'.$ext;				//генерируем уникальное имя для загруженной картинки
	
				$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
							DIRECTORY_SEPARATOR.
							'allarticles'.
							DIRECTORY_SEPARATOR.
							$item['article_id'].
							DIRECTORY_SEPARATOR.
							$newName);
				$form->img->receive();						//получаем само изображение и помещаем в папку articles
	
				$item->img_id = NULL;
				$item->img = $newName;
				$item->save();								//сохраняем строку в базе
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Новая фотография с описанием была успешно добавлена к статье.');
				$this->_redirect('/admin/articles/display/'.$item['article_id']);
			}
		}
	}
	
	public function editblockAction()
	{
		$form = new Redsonya_Form_ArticleEditBlock();
		$this->view->form = $form;
	
		//если получен запрос Post с формой
		if ($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost();
			if ($form->isValid($postData)) {
	
				$input = $form->getValues();				//получаем данные из формы
				$item = Doctrine::getTable('Redsonya_Model_ArticlesImg')->find($input['img_id']);	//находим строку по id-шнику, которую нужно обновить
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
	
					$newName = 'art'.time().'.'.$ext;		//генерируем уникальное имя для загруженной картинки
	
					$form->img->addFilter('Rename', realpath(dirname('.')).		//переименовываем ее
							DIRECTORY_SEPARATOR.
							'allarticles'.
							DIRECTORY_SEPARATOR.
							$item['article_id'].
							DIRECTORY_SEPARATOR.
							$newName);
					$form->img->receive();					//получаем само изображение и помещаем в папку allarticles
					$imgName = $newName;					//задаем ей новое имя
	
					unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
					DIRECTORY_SEPARATOR.
					'allarticles'.
					DIRECTORY_SEPARATOR.
					$item['article_id'].
					DIRECTORY_SEPARATOR.
					$oldImgName);
				}
				$item->img = $imgName;
				$item->save();								//обновляем строку в базе
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Блок с фотографией и описанием был успешно обновлен.');
				$this->_redirect('/admin/articles/display/'.$input['article_id']);
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
					->from('Redsonya_Model_ArticlesImg i')
					->where('i.img_id = ?', $input->id);
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->form->populate($result[0]);
					$this->view->block = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Статья не найдена', 404);
				}
			} else {
				throw new Zend_Controller_Action_Exception('Статья не найдена', 404);
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
				$item = Doctrine::getTable('Redsonya_Model_ArticlesImg')->find($del);
				$artid = $item['article_id'];
				$oldImgName = $item['img'];					//сохраняем имя старой фотки
	
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ArticlesImg i')
					->where('i.img_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографии из статьи. Запись не найдена в базе данных');
	
				//удаляем фотку
				unlink(realpath(dirname('.')).			//удаляем старое фото из каталога
					DIRECTORY_SEPARATOR.
					'allarticles'.
					DIRECTORY_SEPARATOR.
					$artid.
					DIRECTORY_SEPARATOR.
					$oldImgName);
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Фотография и описание к ней были успешно удалены из статьи.');
				$this->_redirect('/admin/articles/display/'.$artid);
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
					->from('Redsonya_Model_ArticlesImg i')
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
	
				//находим имена всех фоток в данной статье из таблицы ArticlesImg
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ArticlesImg i')
					->where('i.article_id = ?', $del);
				$images = $q->fetchArray();
	
				//удаляем их из директории
				foreach ($images as $img) {
					unlink(realpath(dirname('.')).DIRECTORY_SEPARATOR.'allarticles'.DIRECTORY_SEPARATOR.$del.DIRECTORY_SEPARATOR.$img['img']);
				}
	
				//удаляем директорию с именем идентификатора класса
				rmdir(realpath(dirname('.')).DIRECTORY_SEPARATOR.'allarticles'.DIRECTORY_SEPARATOR.$del);
	
				//удаляем все записи из таблицы фоток ArticlesImg
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ArticlesImg i')
					->where('i.article_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении фотографий статьи из базы данных.');
	
				//удаляем запись о самой статье с таблицы Articles
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_Articles a')
					->where('a.article_id = ?', $del);
				$result = $q->execute();
				if(!$result) throw new Zend_Controller_Action_Exception('Ошибка при удалении статьи из базы данных.');
				
				//удаляем все комментарии к данной статье
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ArticleComments c')
					->where('c.article_id = ?', $del);
				$result = $q->execute();
				
				//вывод сообщения об удалении и редирект на список статей
				$this->_helper->getHelper('FlashMessenger')->addMessage('Статья была успешно удалена.');
				$this->_redirect('/admin/articles/index');
			}
		}
		else
		{
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
	
			//если передан правильный идентификатор статьи, находим ее название и передаем результат в вид для подтверждения ее удаления
			if ($input->isValid()) {
				$artid = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_Articles a')
					->where('a.article_id = ?', $artid);
					
				$result = $q->fetchArray();
				if (count($result) == 1) {
					$this->view->article = $result[0];
				} else {
					throw new Zend_Controller_Action_Exception('Статья не найдена', 404);
				}
			}
		}
	}
	
	public function commentsAction()
	{
		if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
			$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();}
	
		//фильтруем идентификатор статьи
		$filters = array('id'	=> array('HtmlEntities', 'StripTags', 'StringTrim'),
						'page'	=> array('HtmlEntities', 'StripTags', 'StringTrim'));
		$validators = array('id'	=> array('NotEmpty', 'Int'),
							'page'	=> array('Int'));
		$input = new Zend_Filter_Input($filters, $validators);
		$input->setData($this->getRequest()->getParams());

		//получаем комментарии к данной статье
		if ($input->isValid()) {
			$articleid = $input->id;
				
			//получаем статью, к которой относятся комментарии, для вывода названия в заголовке
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_Articles a')
				->where('a.article_id = ?', $articleid);
			$result = $q->fetchOne();
			$this->view->article = $result;
				
			//получаем комментарии
			$q = Doctrine_Query::create()
				->from('Redsonya_Model_ArticleComments c')
				->where('c.article_id = ?', $articleid)
				->orderBy('c.created DESC');
				
			$perPage = 3;
			$numPageLinks = 5;
				
			//инициализация компонента разбиения на страницы
			$pager = new Doctrine_Pager($q, $input->page, $perPage);
				
			//выполняем запрос учитывающий номер страницы
			$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
				
			//инициализация макета компонанта для разбиения на страницы
			$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
			$pagerUrlBase = $this->view->url(array('id' => $articleid), 'admin-articles-comments', 1) . "/{%page}";
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
				//находим id-шник статьи к которой относится удаляемый комментарий для редиректа
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ArticleComments c')
					->where('c.comment_id = ?', $del);
				$result = $q->fetchArray();
				$article_id = $result[0]['article_id'];
	
				//удаляем запись из базы
				$q = Doctrine_Query::create()
					->delete('Redsonya_Model_ArticleComments c')
					->where('c.comment_id = ?', $del);
				$result = $q->execute();
	
				$this->_helper->getHelper('FlashMessenger')->addMessage('Комментарий был успешно удален');
				$this->_redirect('/admin/articles/comments/'.$article_id);
			}
			else {
				throw new Zend_Controller_Action_Exception('Комментарий не найден');
			}
		}
		else
		{
			//для подтверждения удаления комментария статьи
			//фильтруем GET запрос
			$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
			$validators = array('id' => array('NotEmpty', 'Int'));
			$input = new Zend_Filter_Input($filters, $validators);
			$input->setData($this->getRequest()->getParams());
	
			//если передан правильный идентификатор комментария, находим его и передаем результат в вид
			if ($input->isValid()) {
				$comm_id = $input->id;
				$q = Doctrine_Query::create()
					->from('Redsonya_Model_ArticleComments с')
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