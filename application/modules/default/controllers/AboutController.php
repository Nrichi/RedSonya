<?php

class AboutController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	$q = Doctrine_Query::create()
    	->from('Redsonya_Model_Masters m');
    	$result = $q->fetchArray();
   		$this->view->masters = $result;

    	$q = Doctrine_Query::create()
    	->from('Redsonya_Model_AboutPosts p');
    	$result = $q->fetchArray();
   		$this->view->posts = $result;
    }
}