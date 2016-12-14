<?php
	//адаптер для подключения к базе, основанный на Doctrine (стр.180)
class Redsonya_Auth_Adapter_Doctrine implements Zend_Auth_Adapter_Interface
{
	//массив, содержащий запись об аутентифицированном пользователе
	protected $_resultArray;
	
	//конструктор, принимает имя пользователя и пароль
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}
	
	//основной метод футентификации
	//запрашивает базу данных на предмет наличия подходящих учетных данных
	//возвращает экземпляр Zend_Auth_Result с кодом успеха\неудачи
	public function authenticate()
	{
		$q = Doctrine_Query::create()
		->from('Redsonya_Model_User u')
		->where('u.username = ? AND u.password = ?',
                    array($this->username, $this->password)
          );
		$result = $q->fetchArray();
		if (count($result) == 1) {
			$this->_resultArray = $result[0];
			return new Zend_Auth_Result(
					Zend_Auth_Result::SUCCESS, $this->username, array());
		} else {
			return new Zend_Auth_Result(
					Zend_Auth_Result::FAILURE, null,
					array('Ошибка авторизации')
			);
		}		
	}
	
	// возвращает результирующий массив, представляющий собой запись
	// об аутентифицированном пользователе
	//если требуется, удаляет из записи указанные поля
	public function getResultArray($excludeFields = null)
	{
		if (!$this->_resultArray) {
			return false;
		}
	
		if ($excludeFields != null) {
			$excludeFields = (array)$excludeFields;
			foreach ($this->_resultArray as $key => $value) {
				if (!in_array($key, $excludeFields)) {
					$returnArray[$key] = $value;
				}
			}
			return $returnArray;
		} else {
			return $this->_resultArray;
		}
	}
}