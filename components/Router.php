<?php

Class Router {

	private $routes = array();

	public function __construct() {
		$this->routes = include(ROOT . "/config/routes.php");
	}

	public function run() {
		$uri = empty($_SERVER['REQUEST_URI']) ? "" : trim($_SERVER['REQUEST_URI'], '/');
		
		foreach ($this->routes as $route => $path) {

			if (preg_match("~$route~", $uri)) {
				$internalRoute = preg_replace("~$route~", $path, $uri);

				$segments = explode("/", $internalRoute);

				$controllerName = ucfirst(array_shift($segments)) . "Controller";
				$actionName = "action" . ucfirst(array_shift($segments));

				$controllerFile = ROOT . '/controllers/' . $controllerName . '.php';
				
				if (file_exists($controllerFile)) {
					include_once($controllerFile);

					$object = new $controllerName;
					if (method_exists($object, $actionName)) {
						$result = call_user_func_array(array($object, $actionName), $segments);

						if ($result != null)
							break;
					}
				}

			} //preg match

		} // foreach

	}



}

?>