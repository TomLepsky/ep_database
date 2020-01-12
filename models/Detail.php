<?php

class Detail extends Product {

	public $material = array();

	public function __construct(string $prefix, int $classifier, int $number, string $name, int $nodeType, int $parentId, 
		int $quantity, int $ownerId, string $ownerName, int $status, string $path, string $note, int $sheetFormat, 
		int $version, int $creationDate = -1,  int $id = -1) {
		parent::__construct($prefix, $classifier, $number, $name, $nodeType, $parentId, 
		$quantity, $ownerId, $ownerName, $status, $path, $note, $sheetFormat, 
		$version, $creationDate = -1, $id = -1);

		$this->extension2D = ".dwg";
		$this->extension3D = ".3D.dwg";
	} 

	public function collectPrice() : float {
		$this->price = 0.0; 
		foreach ($this->material as $m) {
			$this->price += $m->price * $m->quantity;
		}

		return $this->price * $this->quantity;
	}

	public function add() : bool {

		if (Node::isNodeChildbearing($this->parentId)) {
		
			parent::add();

			if (!isset($this->material))
				$this->setMaterial(Material::getDefaultMaterial());

			$result = $this->db->prepare("insert INTO `matching_materials_nodes` 
											(`node_id`, `match_id`, `quantity`) 
											VALUES (?, ?, ?)"
			);

			foreach ($this->material as $m) {
				$result->execute(array($this->id, 
									   $m->matchingId, 
									   $m->quantity
				));
			}

			return true;
		} 

		return false;
	} 

	public function update(string $oldPath) : bool {

		if (!isset($this->material))
				$this->setMaterial(Material::getDefaultMaterial());

		$result = $this->db->prepare("update `matching_materials_nodes` SET 
										`match_id`= ?, `quantity`= ? WHERE node_id = ?");

		$this->material = $this->material[0];

		$result->execute(array($this->material->matchingId,
							   $this->material->quantity,
							   $this->id
		));

		return parent::update($oldPath);
	}

	public function delete() : bool {

		parent::delete();

		$result = $this->db->prepare("delete FROM `matching_materials_nodes` WHERE node_id = ?");
		return $result->execute(array($this->id));
	}

	public function setMaterial($material) : void {
		$this->material = array();
		$this->material[] = $material;
	}

}

?>