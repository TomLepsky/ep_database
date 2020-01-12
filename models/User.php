<?php

class User {
	
	public $id;
	public $login;
	public $name;
	public $role;
	public $ip;
	public $destinationFolderToCopy;

	public function __construct(int $id, string $name, string $role = 'user', string $destinationFolderToCopy = "" . DIRECTORY_SEPARATOR) {
		$this->id = $id;
		$this->name = $name;
		$this->role = $role;
		$this->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
		//$this->destinationFolderToCopy = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $this->ip . DIRECTORY_SEPARATOR . "D:" . DIRECTORY_SEPARATOR;
		$this->destinationFolderToCopy = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $this->ip . DIRECTORY_SEPARATOR;
	}

	public static function getUsers() : array {
		$db = DB::getConnection();

		$result = $db->query("select * from users");
		$result = $result->fetchAll();

		$users = array();
		foreach ($result as $elem) {
			$users[] = new User($elem['id'], $elem['name'], $elem['role'], $elem['destination_folder_to_copy']);
		}
		return $users;
	}

	public static function register($login, $email, $password) {
		
		$db = DB::getConnection();
		
		$sql = "insert INTO user (name, email, password, role) VALUES (:login, :password, 'user')";
		
		$result = $db->prepare($sql);
		$result->bindValue(':login', $name, PDO::PARAM_STR);
		$result->bindValue(':password', $password, PDO::PARAM_STR);
		
		$result->execute();
		
		return $result;
		
	}


	public static function checkUserData($login, $password) {
		
		$db = DB::getConnection();
		
		$query = 'select * FROM users WHERE login = :login AND password = :password';
		
		$result = $db->prepare($query);
		$result->bindValue(':login', $login);
		$result->bindValue(':password', $password);
		$result->execute();
		
		$user = $result->fetch();
		if ($user) {
			return $user['id'];
		}
		
		return false;
		
	}
	
	
	public static function auth(User $user) : void {
		$_SESSION['id'] 						= $user->id;
		$_SESSION['user'] 						= $user->name;
		$_SESSION['role'] 						= $user->role;
		$_SESSION['ip'] 						= $user->ip;
		$_SESSION['destination_folder_to_copy'] = $user->destinationFolderToCopy;
	}
	
	
	public static function checkLogged() {
		
		if (isset($_SESSION['user']))
			return $_SESSION['user'];
			
		header("Location: /user/login");
		
	}
	
	
	public static function isGuest() {
		
		if (isset($_SESSION['user']))
			return false;
		
		return true;
	}

	public static function isAdmin() {
		if (!self::isGuest())
			if ($_SESSION['role'] == 'admin')
				return true;
		return false;
	}
	
	
	public static function getUserById($userId) {
		
		$db = DB::getConnection();
		
		$query = 'SELECT * FROM user WHERE id = :id';
		
		$result = $db->prepare($query);
		$result->bindValue(':id', $userId);
		$result->execute();
		
		return $result->fetch();
		
	}


	public static function isUserExist($login, $password) {
		$db = DB::getConnection();

		$query = "select count(*) from users where login = :login AND password = md5(:password)";
		$result = $db->prepare($query);
		$result->bindValue(':login', $login);
		$result->bindValue(':password', $password);
		$result->execute();

		if ($result->fetchColumn())
			return true;

		return false;

	}


	public static function getUserIdByLogin($login) : User {
		$db = DB::getConnection();

		$result = $db->prepare("select * from users where login = ?");
		$result->execute(array($login));
		$result = $result->fetch();
		
		return new User($result['id'], $result['name'], $result['role']);
	}

	public static function goOut() {
		if (isset($_SESSION['user']))
			unset($_SESSION['user']);
	} 
	
	
}

?>