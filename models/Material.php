<?php

class Material {
	
	public $id;
	public $name;
	public $quantity;
	public $measure;
	public $price;
	public $producerName;
	public $producerId;
	public $matchingId;

	protected $db;

	protected const DEFAULT_MATERIAL_ID = 1;

	public function __construct($name, $measure, $price, $producerName, $quantity = 0) {
		$this->name = $name;
		$this->quantity = floatval(str_replace(",", ".", strval($quantity)));
		$this->measure = $measure;
		$this->price = $price;
		$this->setProducer($producerName);

		$this->db = DB::getConnection();
	}

	private function setProducer(string $producerName) : void {
		$db = DB:: getConnection();
		$result = $db->prepare("select id from producers where name = ?");
		$result->execute(array($producerName));
		$result = $result->fetch();

		if (empty($result)) {
			$this->producerId = 1;
			$this->producerName = 'Нет производителя';
		}
		else {
			$this->producerId = $result['id'];
			$this->producerName = $producerName;
		}
	}

	public static function factory(array $data) : Material {
		$material = new Material(
							$data['material_name'],					
							$data['measure'],
							$data['price'],
							$data['producer_name'],
							$data['material_quantity']
		);
		
		$material->id 			= $data['material_id'];
		$material->matchingId 	= $data['match_id'];
		
		return $material;
	}

	public static function getMaterialsById(int $id) : array {
		$db = DB::getConnection();

		$result = $db->prepare("select 
									t1.id, t1.name AS material_name, t1.measure, 
									t2.price, t2.id AS matching_id
									t3.name AS producer_name

								FROM materials t1 
									INNER JOIN matching_materials t2 	ON t2.material_id = t1.id 
								    INNER JOIN producers t3 			ON t2.producer_id = t3.id

								WHERE t1.id = ?"
		);

		$result->execute(array($id));
		$result = $result->fetchAll();

		$material = array();
		if (!empty($result)) {
			foreach ($result as $elem) {
				$m = new Material($elem['material_name'],
								  $elem['measure'],
								  $elem['price'],
								  $elem['producer_name']

				);
				$m->id = $elem['id'];
				$material[] = $m;
			}
		}

		return $material;
	}

	public static function getMaterials() : array {
		$db = DB::getConnection();

		$result = $db->prepare("select 
									t1.id, t1.name AS material_name, t1.measure, 
									t2.price, t2.id AS matching_id,
									t3.name AS producer_name, t3.id AS producer_id

								FROM materials t1 
									INNER JOIN matching_materials t2 	ON t2.material_id = t1.id 
								    INNER JOIN producers t3 			ON t2.producer_id = t3.id"
		);

		$result->execute();
		$result = $result->fetchAll();

		$material = array();
		if (!empty($result)) {
			foreach ($result as $elem) {
				$m = new Material($elem['material_name'],
								  $elem['measure'],
								  $elem['price'],
								  $elem['producer_name']
				);
				$m->id = $elem['id'];
				$m->matchingId = $elem['matching_id'];
				$material[] = $m;
			}
		}

		return $material;
	}

	public static function getMaterialByIdAndProducer(int $materialId, int $producerId) {
		$db = DB::getConnection();

		$result = $db->prepare("select 
									t1.id, t1.name AS material_name, t1.measure, 
									t2.price, t2.id AS matching_id,
									t3.name AS producer_name

								FROM materials t1 
									INNER JOIN matching_materials t2 	ON t2.material_id = t1.id
								    INNER JOIN producers t3 			ON t2.producer_id = t3.id

								WHERE t1.id = ? AND t3.id = ?"
		);

		$result->execute(array($materialId, $producerId));
		$result = $result->fetch();

		$material = null;
		if (!empty($result)) {
			$material = new Material($result['material_name'],
								    $result['measure'],
								    $result['price'],
								    $result['producer_name']

			);
			$material->id = $result['id'];
			$material->matchingId = $result['matching_id'];
		}

		return $material;
	}

	public static function getMaterialByMatchingId(int $matchingId) : Material {
		$db = DB::getConnection();

		$result = $db->prepare("select t1.id, t1.name AS material_name, t1.measure, 
									t2.price, t2.id AS matching_id,
									t3.name AS producer_name

								FROM materials t1 
									INNER JOIN matching_materials t2 	ON t2.material_id = t1.id
								    INNER JOIN producers t3 			ON t2.producer_id = t3.id

								WHERE t2.id = ?"
		);

		$result->execute(array($matchingId));
		$result = $result->fetch();

		if (!empty($result)) {
			$material = new Material($result['material_name'],
								    $result['measure'],
								    $result['price'],
								    $result['producer_name']

			);
			$material->id = $result['id'];
			$material->matchingId = $result['matching_id'];
			return $material;
		} else {
			return Material::getDefaultMaterial();
		}
	}

	public static function getDefaultMaterial() : Material {
		return getMaterialByMatchingId(Material::DEFAULT_MATERIAL_ID); 
	}

	public function add() : bool {
		

		if ($this->isNodeExists())
			return false;

		$result = $db->prepare("insert INTO `materials`(`name`, `measure`) VALUES (?, ?)");
		$result->execute(array($this->name, $this->measure));
		
		$this->id = $db->lastInsertId();
		
		$result = $db->prepare("insert INTO `matching_materials` (`material_id`, `producer_id`, `price`) VALUES (?, ?, ?)");

		return $result->execute(array($this->id, $this->producerId, $this->price));
	}

	public function update() : bool {
		$result = $this->db->prepare("update `materials` SET `name`= ?,`measure`= ? WHERE id = ?;
								update `matching_materials` SET `producer_id`= ?,`price`= ? WHERE id = ?;"
		);

		return $result->execute(array(
									$this->name,
									$this->measure,
									$this->id,
									$this->producerId,
									$this->price,
									$this->matchingId
		));
	}

	public function delete() : bool {

		if ($this->isDefault()) 
			return false;

		$result = $this->db->prepare("update `matching_materials_nodes` SET `match_id`= 1, `quantity`= 0 WHERE match_id = ?;
								DELETE FROM `materials` WHERE id = ?;
								DELETE FROM `matching_materials` WHERE material_id = ? AND producer_id = ?"
		);

		return $result->execute(array(
								$this->matchingId,
								$this->id,
								$this->id, 
								$this->producerId
		));
	}

	private function isDefault() : bool {
		return $this->id == Material::DEFAULT_MATERIAL_ID ? true : false;
	}

	private function isMaterialExists() : bool {
		$result = $this->db->prepare("select 
										COUNT(*) as num 

										FROM materials t1 
										    INNER JOIN matching_materials t2 	ON t2.material_id = t1.id
										    INNER JOIN producers t3 			ON t2.producer_id = t3.id

									    WHERE t1.id = ? AND t3.id = ?"
		);
		
		$result->execute(array($this->id, $this->producerId));
		$result = $result->fetch();

		return $result['num'] == 0 ? false : true;
	}

}

?>