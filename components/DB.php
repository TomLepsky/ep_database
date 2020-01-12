<?php

class DB {
	
	private static $db;

	private function __conctrust() {
		
	}

	public static function getConnection() {
		if (empty(self::$db)) {
			$params = include(ROOT . '/config/db_params.php');
		
			$dsn = "mysql:host={$params['host']};dbname={$params['dbname']};chartset=utf8";
			try {
				$opt = [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
				];
				self::$db = new PDO($dsn, $params['user'], $params['password'], $opt);
				
			} catch (PDOException $e) {
				echo "DB error: " . $e->getMessage();
				exit();
			}
		}
		
		return self::$db;
		
	}
	
}

?>