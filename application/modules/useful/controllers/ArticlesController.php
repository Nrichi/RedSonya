<?php

class Useful_ArticlesController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->doctype('XHTML1_STRICT');
	}
	
	public function indexAction()
    {
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
    		$pagerUrlBase = $this->view->url(array(), 'articles', 1) . "/{%page}";
    		$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
    	
    		//устанавливаем шаблон для отображения ссылки на страницу
    		$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
    		$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
    		$pagerLayout->setSeparatorTemplate('&nbsp;');
    	
    		//присваиваем значения переменным представления
    		$this->view->allarticles = $result;
    		$this->view->pages = $pagerLayout->display(null, true);
    	} else {
    		throw new Zend_Controller_Action_Exception('Страница не найдена');
    	}
    }
    
    public function displayAction()
    {
    	//отображаем статью со всеми ее блоками и картинками
    	//фильтруем GET запрос
    	$filters = array('id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
    	$validators = array('id' => array('NotEmpty', 'Int'));
    	$input = new Zend_Filter_Input($filters, $validators);
    	$input->setData($this->getRequest()->getParams());
    	//если передан правильный идентификатор статьи, извлекаем все блоки с фотками и текстовым описанием из бд и передаем результат в вид
    	if ($input->isValid()) {
    		//получаем информацию о статье
    		$articleid = $input->id;
    		$article = Doctrine::getTable('Redsonya_Model_Articles')->find($articleid);
    		$this->view->article = $article;
    			
    		//получаем все блоки с картинками
    		$q = Doctrine_Query::create()
    			->from('Redsonya_Model_ArticlesImg i')
    			->where('i.article_id = ?', $articleid)
    			->orderBy('i.img_id ASC');
    		$result = $q->fetchArray();
    		$this->view->blocks = $result;
    	} else {
    		throw new Zend_Controller_Action_Exception('Страница не найдена', 404);
    	}
    	
    	//выводим форму для комментария
    	$form = new Redsonya_Form_ArticleComment($articleid);
    	$this->view->form = $form;
    	 
    	//если отправлена форма с комментарием, проверяем данные, сохраняем в базу и выводим сообщение об успешной отправке
    	if ($this->getRequest()->isPost()) {
    		if ($form->isValid($this->getRequest()->getPost())) {
    			$comt = new Redsonya_Model_ArticleComments();
    			$comt->fromArray($form->getValues());
    			 
    			$comt->comment_id = null;
    			$comt->created = time();
    			$comt->save();
    	
    			//если комментарий успешно сохранен - отправляем уведомление на e-mail
    			$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
    			$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
    			$to = $localConfig->global->defaultEmailAddress;
    			$mail = new Zend_Mail('UTF-8');
    			$mail->setBodyText('На сайте мастерской "Рыжая Соня" добавлен новый комментарий к статье.
Имя: '.$comt['name'].'
Текст комментария:
    	
'.$comt['comment']);
    			$mail->setFrom($comt['email']);
    			$mail->addTo($to);
    			$mail->setSubject('Новый комментарий на сайте "Рыжая Соня"');
    			$mail->send();
    	
    			$this->_redirect('/articles/display/'.$articleid);
    		}
    	}
    	 
    	//выводим все комментарии к посту
    	$q = Doctrine_Query::create()
    	->from('Redsonya_Model_ArticleComments a')
    	->where('a.article_id = ?', $articleid)
    	->orderBy('a.created DESC');
    	 
    	$result = $q->fetchArray();
    	$this->view->comments = $result;
    }
}