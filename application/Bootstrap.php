<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDoctrine()
	{
		require_once 'Doctrine/Doctrine.php';
		$this->getApplication()
		->getAutoloader()
		->pushAutoloader(array('Doctrine', 'autoload'), 'Doctrine');
	
		$manager = Doctrine_Manager::getInstance();
		$manager->setCharset("utf8");
		$manager->setCollate("utf8_general_ci");
		$manager->setAttribute(
				Doctrine::ATTR_MODEL_LOADING,
				Doctrine::MODEL_LOADING_CONSERVATIVE
		);
	
		$config = $this->getOption('doctrine');
		$conn = Doctrine_Manager::connection($config['dsn'], 'doctrine');
		
		// PDO
		$dbh = $conn->getDbh();
		$sql = "SET character_set_results = 'utf8', character_set_client = 'utf8',
				character_set_connection = 'utf8', character_set_database = 'utf8',
				character_set_server = 'utf8'";
		$dbh->query($sql);
		mb_internal_encoding("UTF-8");
		return $conn;
	}
}