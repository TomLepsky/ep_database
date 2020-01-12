<?php

class Autoloader {
	
	private static function loadClasses($className) {
		
		$paths = array(
							'/models/',
							'/components/',
							'/controllers/'
							);
							
		$className = str_replace('\\', '/', $className) . '.php';

		foreach ($paths as $path) {
			$path = ROOT . $path . $className;
			if (file_exists($path))
				include_once $path;
		}
		
	}
	
	public static function run() {
		spl_autoload_register(array('Autoloader', 'loadClasses'));
	}
	
	
}

?>