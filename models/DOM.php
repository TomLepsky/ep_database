<?php

class DOM {
	
	protected $db;

	public $projects = array();
	public $materials = array();
	public $users = array();
	public $statuses = array();

	public function __construct() {
		$this->db = DB::getConnection();
	}

	private function getProjectsIds() : array {
		$result = $this->db->query("select * from projects");
		
		return $result->fetchAll();
	}

	public function loadProjects() : array {
		$ids = $this->getProjectsIds();
		if (empty($ids)) 
			return array();

		foreach ($ids as $id) {
			$node = Node::getNodeById($id['project_id'],
									  0, 
									  false,
									  $id['quantity']
			);
			//$node->collectPrice();
			//$node->realNode();
			$this->projects[] = $node;
		}
		return $this->projects;
	}

	public function loadMaterials() : array {
		return $this->materials = Material::getMaterials();
	}

	public function loadUsers() : array {
		return $this->users = User::getUsers();
	}

	public function loadStatuses() : array {
		return $this->statusses = Status::getStatuses();
	}

	public function calcutalePrice() : bool {
		
	}

}

?>