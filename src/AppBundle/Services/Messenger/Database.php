<?php 

namespace AppBundle\Services\Messenger;

class Database {
	
	private static $instance;
		
	private function __construct(){}

	public static function getInstance($dbConfig = false){
		if(empty( self::$instance )){			
			self::$instance = new \PDO("mysql:host=" . $dbConfig->server . ";dbname=" . $dbConfig->name,$dbConfig->user,$dbConfig->password);
			self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		}		
		return self::$instance;
	}
	
	
}
