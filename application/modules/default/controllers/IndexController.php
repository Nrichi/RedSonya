<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	$q = Doctrine_Query::create()
    	->select('i.img')
    	->from('Redsonya_Model_Items i')
    	->where('i.category_id != ?', '5')	//не материалы, а изделия
    	->orderBy('i.created DESC')
    	->limit(10);
    	$result = $q->fetchArray();
    	$this->view->images = $result;


    	$q = Doctrine_Query::create()
    	->from('Redsonya_Model_ItemCategories c')
    	->where('c.category_id != ?', '5')	//не материалы, а изделия
    	->limit(4);
    	$result = $q->fetchArray();
    	$this->view->categories = $result;
    }
}