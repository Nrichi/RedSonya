<?php

class Useful_LinksController extends Zend_Controller_Action
{
	public function indexAction()
    {
    	$q = Doctrine_Query::create()
    	->from('Redsonya_Model_Links l');
    	
    	$result = $q->fetchArray();
    	$this->view->links = $result;
    }
}