<?php

class QuestionsController extends Zend_Controller_Action
{
    public function indexAction()
    {
    	$q = Doctrine_Query::create()
    	->from('Redsonya_Model_Questions q');
    	$result = $q->fetchArray();
   		$this->view->questions = $result;
    }
}