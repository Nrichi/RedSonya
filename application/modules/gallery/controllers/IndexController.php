<?php

class Gallery_IndexController extends Zend_Controller_Action
{
	public function init()
	{
		$this->view->doctype('XHTML1_STRICT');
	}
	
	public function indexAction()
    {
    	$filters = array(
    			'category'	=> array('HtmlEntities', 'StripTags', 'StringTrim'),
    			'page' 		=> array('HtmlEntities', 'StripTags', 'StringTrim'));
    	$validators = array(
    			'category'	=> array('Alpha', 
    								 array('InArray', 'haystack' => 
											array('teddy', 'wool', 'decor', 'other'))),
    			'page'		=> array('Int'));
    	$input = new Zend_Filter_Input($filters, $validators);
    	$input->setData($this->getRequest()->getParams());
    	
    	if ($input->isValid()) {
    		
    		switch ($input->category) {
    			case 'teddy': $cat = 1; break;
    			case 'wool'	: $cat = 2; break;
    			case 'decor': $cat = 3; break;
    			case 'other': $cat = 4; break;
    		}
    		
    		$q = Doctrine_Query::create()
    		->select('i.item_id, i.item_name, i.img')
    		->from('Redsonya_Model_Items i')
    		->where('i.category_id = ?', $cat)
    		->addWhere('i.sold = ?', '1');
  		
    		$perPage = 12;
    		$numPageLinks = 5;
    		$pager = new Doctrine_Pager($q, $input->page, $perPage);
    		$result = $pager->execute(array(), Doctrine::HYDRATE_ARRAY);
    		
    		//инициализация макета компонанта для разбиения на страницы
    		$pagerRange = new Doctrine_Pager_Range_Sliding(array('chunk' => $numPageLinks), $pager);
    		$pagerUrlBase = $this->view->url(array(), 'gallery', 1) . "/{$input->category}/{%page}";
    		$pagerLayout = new Doctrine_Pager_Layout($pager, $pagerRange, $pagerUrlBase);
    		
    		//устанавливаем шаблон для отображения ссылки на страницу
    		$pagerLayout->setTemplate('<a href="{%url}">{%page}</a>');
    		$pagerLayout->setSelectedTemplate('<span class="active">{%page}</span>');
    		$pagerLayout->setSeparatorTemplate('&nbsp;');
    		
    		//присваиваем значения переменным представления
    		$this->view->items = $result;
    		$this->view->pages = $pagerLayout->display(null, true);
    	} else {
    		throw new Zend_Controller_Action_Exception('Invalid input');
    	}
    }
    
	public function displayAction()
    {
    	//фильтруем GET запрос
    	$filters = array(
    			'id' => array('HtmlEntities', 'StripTags', 'StringTrim'));
    	$validators = array(
    			'id' => array('NotEmpty', 'Int'));
    	$input = new Zend_Filter_Input($filters, $validators);
    	$input->setData($this->getRequest()->getParams());
    	 
    	//если передан правильный идентификатор товара, создаем запрос к бд и передаем результат в вид
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
    		
    		//выводим фотографии товара для слайдера
    		$q = Doctrine_Query::create()
    		->from('Redsonya_Model_ItemImg g')
    		->where('g.item_id = ?', $itemid);
    		 
    		$result = $q->fetchArray();
    		$this->view->images = $result;
    		
    		//отображаем награды и участие в выставках
    		$q = Doctrine_Query::create()
    		->from('Redsonya_Model_Awards a')
    		->leftJoin('a.Redsonya_Model_ItemAward w')
    		->where('w.item_id = ?', $input->id);
    		$result = $q->fetchArray();
    		if($result) $this->view->awards = $result;
    		
    		//отображаем форму для добавления комментариев
    		$form = new Redsonya_Form_ItemComment($itemid);
    		$this->view->form = $form;
    		
    		//если отправлена форма с комментарием, проверяем данные, сохраняем в базу и выводим сообщение об успешной отправке
    		if ($this->getRequest()->isPost()) {
    			if ($form->isValid($this->getRequest()->getPost())) {
    				$comt = new Redsonya_Model_ItemComments();
    				$comt->fromArray($form->getValues());
    				 
    				$comt->comment_id = null;
    				$comt->created = time();
    				$comt->save();
    		
    				//если комментарий успешно сохранен - отправляем уведомление на e-mail
    				$configs = $this->getInvokeArg('bootstrap')->getOption('configs');
    				$localConfig = new Zend_Config_Ini($configs['localConfigPath']);
    				$to = $localConfig->global->defaultEmailAddress;
    				$mail = new Zend_Mail('UTF-8');
    				$mail->setBodyText('На сайте мастерской "Рыжая Соня" добавлен новый комментарий.
Имя: '.$comt['name'].'
Текст комментария:

'.$comt['comment']);
    				$mail->setFrom($comt['email']);
    				$mail->addTo($to);
    				$mail->setSubject('Новый комментарий на сайте "Рыжая Соня"');
    				$mail->send();
    		
    				$this->_redirect('/gallery/display/'.$itemid);
    			}
    		}
    		 
    		//выводим все комментарии к посту
    		$q = Doctrine_Query::create()
    		->from('Redsonya_Model_ItemComments c')
    		->where('c.item_id = ?', $itemid)
    		->orderBy('c.created DESC');
    		 
    		$result = $q->fetchArray();
    		$this->view->comments = $result;
    	
    	} else {
    		throw new Zend_Controller_Action_Exception('Страница не существует', 404);
    	}
    }
}