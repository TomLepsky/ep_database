<?php

class Producer {
	
	public $id;
	public $name;

	public function __construct(string $name) {
		$this->name = $name;
	}

	public function add() : bool {
		$db = DB::getConnection();
		$result = $db->prepare("insert INTO `producers`(`name`) VALUES (?)");
		return $result->execute(array($this->name));
	}

	public static function getProducerById(int $id) {
		$db = DB::getConnection();

		$result = $db->prepare("select * from producers where id = ?");
		$result->execute(array($id));
		$result = $result->fetch();

		if (empty($result)) 
			return null;

		$producer = new Producer($result['name']);
		$producer->id = $result['id'];

		return $producer;
	}

	public function delete() : bool {
		$db = DB::getConnection();

		$result = $db->prepare("delete FROM `producers` WHERE id = ?");
		return $result->execute(array($this->id));
	}

}

?>