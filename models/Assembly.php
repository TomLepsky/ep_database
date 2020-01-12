<?php

class Assembly extends Product {

	public function __construct(string $prefix, int $classifier, int $number, string $name, int $nodeType, int $parentId, 
		int $quantity, int $ownerId, string $ownerName, int $status, string $path, string $note, int $sheetFormat, 
		int $version, int $creationDate = -1,  int $id = -1) {
		parent::__construct($prefix, $classifier, $number, $name, $nodeType, $parentId, 
		$quantity, $ownerId, $ownerName, $status, $path, $note, $sheetFormat, 
		$version, $creationDate = -1, $id = -1);

		$this->extension2D = ".dwg";
		$this->extension3D = ".3D.dwg";
	}

	public function add() : bool {

		if (Node::isNodeChildbearing($this->parentId)) {
			
			parent::add();

			if ($this->parentId == 0) {

				$result = $this->db->prepare("insert INTO `projects`(`project_id`, 
												`quantity`) VALUES (?, ?)");
				$result->execute(array($this->id,
									   $this->quantity
				));
			} 

			return true;		
		} 
		
		return false;		
	} 

	public function delete() : bool {

		if (!$this->isEmpty())
			return false;
		
		parent::delete();

		$result = $this->db->prepare("delete FROM `projects` WHERE project_id = ?");
		return $result->execute(array($this->id));
	}
/*
	public function createPath() : string {
		//return $this->number . DIRECTORY_SEPARATOR . $this->number . $this->extension;
		return DOCUMENTATION . DIRECTORY_SEPARATOR . $this->fileName . $this->extension;
	}
*/
	public function collectPrice() : float {
		$this->price = 0.0;
		if (is_array($this->child)) {
			foreach ($this->child as $node) {
				$this->price += $node->collectPrice();
			}
		}
		return $this->price * $this->quantity;
	}

	public function copy(int $copiedNodeId) : bool {
		$result = $this->db->prepare("select COUNT(*) AS num FROM `matching_nodes` 
										WHERE parent_id = ? AND child_id = ?");
		$result->execute(array($this->id,
								$copiedNodeId
		));

		$result = $result->fetch();

		if ($result['num'] == 0) {
			$result = $this->db->prepare("insert INTO `matching_nodes` 
								(`parent_id`, `child_id`, `child_count`) 
								VALUES (?, ?, ?)");
			return $result->execute(array($this->id,
										  $copiedNodeId,
										  $this->quantity
			));
		}

		return false;	
	}

	public function isDescendant(int $id) : bool {
		//parentId stores id of potential child
		if ($id == 0)
			return false;

		if ($id == $this->parentId)
			return true;

		$result = $this->db->prepare(
								"select
									parent_id 
								FROM `matching_nodes`
								WHERE child_id = ?"
		);
		$result->execute(array($id));

		foreach ($result->fetchAll() as $row) {
			if ($this->isDescendant($row['parent_id']))
				return true;
		}
		return false;
	}

	public function isEmpty() : bool {
		$result = $this->db->prepare("select COUNT(*) as num FROM `matching_nodes` WHERE parent_id = ?");
		$result->execute(array($this->id));
		$result = $result->fetch();

		return $result['num'] == 0 ? true : false;
	}

}

?>