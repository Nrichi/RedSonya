<?php
class ContactController extends Zend_Controller_Action
{

  public function init()
  {
    $this->view->doctype('XHTML1_STRICT');
  }
  
  public function indexAction()
  {
    $form = new Redsonya_Form_Message();
    $this->view->form = $form;             
    if ($this->getRequest()->isPost()) {
      if ($form->isValid($this->getRequest()->getPost())) {
        $values = $form->getValues();
        
        //записываем сообщение в базу
        $msg = new Redsonya_Model_Messages();
        $msg->fromArray($values);
        
        $msg->message_id = NULL;
        $msg->created = time();
        $msg->save();

        $configs = $this->getInvokeArg('bootstrap')->getOption('configs');
        $localConfig = new Zend_Config_Ini($configs['localConfigPath']);  
        $to = $localConfig->global->defaultEmailAddress;
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText('На сайте мастерской "Рыжая Соня" получено новое сообщение с контактной формы.
Имя: '.$values['name'].'
Текст сообщения:

'.$values['text']);
        $mail->setFrom($values['email'], $values['name']);
        $mail->addTo($to); 
        $mail->setSubject('Сообщение с контактной формы сайта "Рыжая Соня"');
        //$mail->send();
        $this->_helper->getHelper('FlashMessenger')->addMessage('Спасибо. Ваше сообщение успешно отправлено.');
        $this->_redirect('/contact/success');
      }           
    } 
  }
  
  public function successAction()
  {
    if ($this->_helper->getHelper('FlashMessenger')->getMessages()) {
      $this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();    
    } else {
      $this->_redirect('/');    
    } 
  }
}