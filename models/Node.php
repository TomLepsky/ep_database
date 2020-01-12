<?php

Abstract class Node {

	protected $db;

	public $id;
	public $parentId;
	public $name;
	public $suffix;
	public $path;
	public $child;
	public $status;
	public $note;
	public $fileName;
	public $ownerId;
	public $ownerName;
	public $nodeType;
	public $creationDate;
	public $quantity;
	
	public const NONE 			= 100;
	public const ASSEMBLY 		= 90;
	public const SPECIFICATION 	= 20;
	public const DETAIL 		= 80;
	public const STANDART_PARTS = 70;
	public const OTHER_PARTS 	= 60;
	public const PARTITION		= 110;

	private const DIGIT = 6;

	public const TYPES = array(
							1 	=> Node::OTHER_PARTS, 		
							2 	=> Node::STANDART_PARTS, 
							4 	=> Node::DETAIL, 		
							8 	=> Node::SPECIFICATION,
							16 	=> Node::ASSEMBLY,
							32	=> Node::PARTITION
						);

	public function __construct(string $name, int $nodeType, int $parentId, int $quantity, int $ownerId, 
		string $ownerName, int $status, string $path, string $note, int $creationDate = -1,  int $id = -1) {

		$this->name 		= $name;
		$this->nodeType 	= $nodeType;
		$this->parentId 	= $parentId;		
		$this->quantity 	= $quantity;
		$this->ownerId 		= $ownerId;
		$this->ownerName 	= $ownerName;
		$this->status 		= $status;
		$this->path 		= $path;
		$this->note 		= $note;
		$this->id 			= $id;
		
		if ($creationDate == -1)
			$this->creationDate = time();
		else 
			$this->creationDate = $creationDate;

		$this->db 			= DB::getConnection();
	}

	public static function getNodeById(int $nodeId, int $parentId, int $quantity = 1, $childs, int $mask = 0b110100) : Node {
		$db = DB::getConnection();

		$addiction 	= ' AND parent_id = ?';
		$binding 	= array($nodeId, $parentId);

		if ($parentId == 0) {
			$addiction = '';
			$binding	= array($nodeId);
		}

		$query = 	"select DISTINCT
						t1.id AS node_id, t1.prefix, t1.number, t1.name, t1.suffix, t1.path, t1.sheet_format, t1.creation_date,
						t1.node_type, t1.status, t1.note, t1.parental_ids, t1.version, t1.classifier,
	                    t2.quantity AS material_quantity, t2.match_id,				     
					    t3.price, 
	                    t4.id as material_id, t4.name AS material_name, t4.measure,
					    t5.name as producer_name,
	                    t6.parent_id, t6.quantity,
	                    t7.id AS user_id, t7.name AS owner

					FROM nodes t1 
	                    LEFT JOIN matching_materials_nodes t2 	ON t2.node_id	 	= t1.id
	                    LEFT JOIN matching_materials t3 		ON t2.match_id 		= t3.id
	                    LEFT JOIN materials t4 					ON t3.material_id 	= t4.id
	                    LEFT JOIN producers t5 					ON t3.producer_id 	= t5.id
	                    LEFT JOIN matching_nodes t6 			ON t6.child_id 		= t1.id 
	                    LEFT JOIN users t7 						ON t1.user_id 		= t7.id

				    WHERE t1.id = ? " . $addiction;

		$result = $db->prepare($query);
		$result->execute($binding);
		$result = $result->fetch();

		if (empty($result)) throw new NodeException("Такого проекта не существует.");

		$node = Node::factory($result);

		if ($childs && $node instanceof Assembly) {
			$node->child = Node::getChildNodes($nodeId, $mask);
		}
		
		return $node;
	}

	public static function getChildNodes(int $parentId, int $mask = 0b110100) : array {
		$db = DB::getConnection();

		$addiction = Node::parseMask($mask);

		$query = 	"select DISTINCT
						t1.id AS node_id, t1.prefix, t1.number, t1.name, t1.suffix, t1.path, t1.sheet_format, t1.creation_date,
						t1.node_type, t1.status, t1.note, t1.parental_ids, t1.version, t1.classifier,
	                    t2.quantity AS material_quantity, t2.match_id,				     
					    t3.price, 
	                    t4.id as material_id, t4.name AS material_name, t4.measure,
					    t5.name as producer_name,
	                    t6.parent_id, t6.quantity,
	                    t7.id AS user_id, t7.name AS owner

				    FROM nodes t1 
	                    LEFT JOIN matching_materials_nodes t2 	ON t2.node_id	 	= t1.id
	                    LEFT JOIN matching_materials t3 		ON t2.match_id 		= t3.id
	                    LEFT JOIN materials t4 					ON t3.material_id 	= t4.id
	                    LEFT JOIN producers t5 					ON t3.producer_id 	= t5.id
	                    LEFT JOIN matching_nodes t6 			ON t6.child_id 		= t1.id 
	                    LEFT JOIN users t7 						ON t1.user_id 		= t7.id

				    WHERE t6.parent_id = ? " . $addiction . " ORDER BY t1.node_type ASC, t1.name ASC";

		$result = $db->prepare($query);
		$result->execute(array($parentId));
		$result = $result->fetchAll();

		if (empty($result)) 
			return array();

		$nodes = array();

		for ($i = 0; $i < count($result); $i++) {
			$nodes[] = Node::factory($result[$i]);
		}

		usort($nodes, 	function($a, $b) { 
							if ($a->nodeType == $b->nodeType)
								return 0;
							return $a->nodeType > $b->nodeType ? 1 : -1;
						}
		);

		return $nodes;
	}

	public static function getAllNodes(int $mask = 0b110100) : array {
		$db = DB::getConnection();

		$addiction = Product::parseMask($mask);
		$query = 	"select DISTINCT
						t1.id AS node_id, t1.prefix, t1.number, t1.name, t1.suffix, t1.path, t1.sheet_format, t1.creation_date,
						t1.node_type, t1.status, t1.note, t1.parental_ids, t1.version, t1.classifier

				    FROM nodes t1 

	                ORDER BY node_id ASC";

        $result = $db->query($query);
        $result = $result->fetchAll();

        $nodes = array();
        foreach ($result as $data) {
        	$data['quantity'] 	= 0;
        	$data['user_id'] 	= 0;
        	$data['parent_id'] 	= 0;
        	$data['owner'] 		= 0;
        	$nodes[] = Node::factory($data);
        }
        return $nodes;
	}

	public static function nodeFactory(array $data) : Node {

		switch ($data['node_type']) {

			case Node::DETAIL:
				$node = new Detail(
								$data['prefix'],
								$data['classifier'],
								$data['number'],
								$data['name'], 
								$data['node_type'], 
								$data['parent_id'], 
								$data['quantity'], 
								$data['user_id'],
								$data['owner'], 
								$data['status'], 
								$data['path'], 
								$data['note'], 
								$data['sheet_format'],
								$data['version'],
								$data['creation_date'], 
								$data['id']
							);
				break;

			case Node::ASSEMBLY:
				$node = new Assembly(
								$data['prefix'],
								$data['classifier'],
								$data['number'],
								$data['name'], 
								$data['node_type'], 
								$data['parent_id'], 
								$data['quantity'], 
								$data['user_id'],
								$data['owner'], 
								$data['status'], 
								$data['path'], 
								$data['note'], 
								$data['sheet_format'],
								$data['version'],
								$data['creation_date'], 
								$data['id']
							);
				break;

			case Node::SPECIFICATION:
				$className = 'Specification';
				break;

			case Node::PARTITION:
				$node = new Partition(
								$data['name'], 
								$data['node_type'], 
								$data['parent_id'], 
								$data['quantity'], 
								$data['user_id'],
								$data['owner'], 
								$data['status'], 
								$data['path'], 
								$data['note'], 
								$data['creation_date'], 
								$data['id']
							);
				break;

			case Node::STANDART_PARTS:

				break;

			case Node::OTHER_PARTS:

				break;
				
			default:
				throw new Exception("Нет такого типа узла!", 1);
				break;
		}

		if ($node instanceof Detail) {
			if (isset($data['material_name'])) {
				$node->setMaterial(Material::factory($data));
			}
		}

		$node->realNode();
		
		return $node;
	}

	public function add() : bool {

		$result = $this->db->prepare("insert INTO `nodes` 
										(`name`,`suffix`, `path`, `node_type`, `user_id`, `note`, `creation_date`) 
									VALUES (?, ?, ?, ?, ?, ?, ?)"
		);

		$result->execute(array($this->name,
							   $this->suffix,
							   $this->path,
							   $this->nodeType,
							   $this->ownerId,
							   $this->note,
							   $this->creationDate
		));

		$this->id = $this->db->lastInsertId();

		$result = $this->db->prepare("insert INTO `matching_nodes` 
							(`parent_id`, `child_id`, `child_count`) 
							VALUES (?, ?, 1)"
		);

		return $result->execute(array($this->parentId, 
									   $this->id
		));
	}

	public function update(string $oldPath) : bool {
		$result = $this->db->prepare("update `nodes`
										SET `path` = REPLACE(`path`, ?, ?)
									WHERE `path` LIKE '%" . $oldPath . "%'");
		$result->execute(array($oldPath, $this->fileName));

		$result = $this->db->prepare("uPDATE `nodes` SET 
										`name`= ?, `suffix`=?, `status`=?, `note`= ?, `user_id`= ?, WHERE id = ?"
		);

		return $result->execute(array(
									$this->name,
									$this->suffix,
									$this->status,
									$this->note,
									$this->ownerId,
									$this->id
		));
	}

	public function delete() : bool {
		$result = $this->db->prepare("delete FROM `nodes` WHERE id = ?;
									delete FROM `matching_nodes` WHERE child_id = ? OR parent_id = ?");

		return $result->execute(array($this->id, $this->id, $this->id));
	}

	public static function getNodeTypeById(int $nodeId) : int {
		$db = DB::getConnection();
		
		$result = $db->prepare("select `node_type` FROM `nodes` WHERE id = ?");
		$result->execute(array($nodeId));
		$result = $result->fetch();
		return isset($result['node_type']) ? $result['node_type'] : Node::NONE;
	}

	public function realNode() : void {
		return file_exists(PATH_TO_PROJECTS . $this->path);
		
	}
	
	public function collectPrice() : float {
		return $this->price = 0.0;
	}

	public function updatePrice() : bool {
		$result = $this->db->prepare("update `nodes` SET `price`= ? WHERE id = ?");

		return $result->execute(array($this->price, $this->id));
	}

	public function remove() : bool {
		$result = $this->db->prepare("select COUNT(*) as num FROM `matching_nodes` WHERE child_id = ?");
		$result->execute(array($this->id));
		$result = $result->fetch();

		if ($result['num'] > 1) {
			$result = $this->db->prepare("delete FROM `matching_nodes` 
										WHERE child_id = ? AND parent_id = ?");
			return $result->execute(array($this->id, 
							   			  $this->parentId
			));
		}
		return false;		
	}

	protected static function isNodeChildbearing(int $nodeId) : bool {
		if ($nodeId == 0)
			return true;

		$db = DB::getConnection();

		$nodeId = intval($nodeId);
		$result = $db->prepare("select node_type FROM `nodes` WHERE id = ?");
		$result->execute(array($nodeId));
		$result = $result->fetch();

		return $result['node_type'] == Node::ASSEMBLY || $result['node_type'] == Node::PARTITION ? true : false;
	}

	public static function isNodeTypeExists(int $nodeType) : bool {
		foreach (Node::TYPES as $value => $type) {
			if ($nodeType == $type)
				return true;
		}
		return false;
	}

	public function getParentPath() : string {
		$result = $this->db->prepare("select `path` FROM `nodes` WHERE id = ?");
		$result->execute(array($this->parentId));
		$result = $result->fetch();
		return $result['path'];
	}

	public function createFileName() : string {
		return $this->fileName = $this->name;
	}

	public static function isNodeExists(int $number, int $classifier) : bool {
		$db = DB::getConnection();

		$result = $db->prepare("select COUNT(*) as num FROM `nodes` 
								WHERE `number` = ? AND `classifier` = ?");
		$result->execute(array($number, $classifier));
		$result = $result->fetch();

		return $result['num'] == 0 ? false : true;
	} 

	protected static function parseMask(int $mask) : string {
		$str = '';

		if ($mask < 0)
			$mask = -$mask;

		if ($mask >= (0b1 << Node::DIGIT))
			$mask %= (0b1 << Node::DIGIT);

		/*
		 * 10  20  30  40  50  60 
		 *  x   x   x   x   x   x
		 */
		for ($shift = 0b1 << (Node::DIGIT - 1); $shift > 0b0; $shift >>= 0b1) {
			if (($shift & $mask) == $shift) {
				$str .= ' OR node_type = ' . Node::TYPES[$shift];
			}
		}
		$str = substr($str, 4);

		return empty($str) ? '' : ' AND (' . $str . ')';
	}

}

?>