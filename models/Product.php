<?php

Abstract class Product extends Node {
	
	public $prefix;
	public $number;
	public $exist2D;
	public $exist3D;
	public $sheetFormat;
	public $version;
	public $classifier;

	

	public function __construct(string $prefix, int $classifier, int $number, string $name, int $nodeType, int $parentId, 
		int $quantity, int $ownerId, string $ownerName, int $status, string $path, string $note, int $sheetFormat, 
		int $version, int $creationDate = -1,  int $id = -1) {

		parent::__construct($name, $nodeType, $parentId, $quantity, $ownerId, $ownerName, $status, $path, $note, $creationDate, $id);

		$this->prefix 		= $prefix;
		$this->classifier	= $classifier;
		$this->number 		= Classifier::convertToLeadingZeros($number);
		$this->version 		= $version;

		if ($this->version == 0) 
			$this->suffix 	= '';
		else
			$this->suffix	= " (Изм.$this->version)";

		$this->fileName 	= $prefix . "." . $classifier . "." . $number . " " . $name . $this->suffix;
	}

	public function add() : bool {

		$result = $this->db->prepare("insert INTO `nodes` 
											(`prefix`, `classifier`, `number`, `name`,`suffix`, `path`, `node_type`, `user_id`, 
												`note`, `sheet_format`, `creation_date`) 
											 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);

		$result->execute(array($this->prefix,
							   $this->classifier,
							   $this->number,	
							   $this->name,
							   $this->suffix,
							   $this->path,
							   $this->nodeType,
							   $this->ownerId,
							   $this->note,
							   $this->sheetFormat,
							   $this->creationDate
		));

		$this->id = $this->db->lastInsertId();

		$result = $this->db->prepare("insert INTO `matching_nodes` 
							(`parent_id`, `child_id`, `child_count`) 
							VALUES (?, ?, ?)"
		);

		return $result->execute(array($this->parentId, 
									   $this->id, 
									   $this->quantity
		));
	}

	public function update(string $oldPath) : bool {
		$result = $this->db->prepare("update `nodes`
										SET `path` = REPLACE(`path`, ?, ?)
									WHERE `path` LIKE '%" . $oldPath . "%'");
		$result->execute(array($oldPath, $this->fileName));

		$result = $this->db->prepare("update `matching_nodes` SET `child_count`= ? WHERE `parent_id`= ? AND `child_id`= ?;
										UPDATE `nodes` SET 
									`prefix` = ?, `classifier` = ?, `number`= ?,`name`= ?, `suffix`=?, `status`=?, 
									`note`= ?, `user_id`= ?, `sheet_format`= ? WHERE id = ?"
		);

		return $result->execute(array(
									$this->quantity,
									$this->parentId,
									$this->id,
									$this->prefix,
									$this->classifier,
									$this->number,	
									$this->name,
									$this->suffix,
									$this->status,
									$this->note,
									$this->ownerId,
									$this->sheetFormat,
									$this->id
		));
	}

	public function realNode() : void {
		$this->exist2D 	= file_exists(PATH_TO_PROJECTS . $this->path . $this->extension2D);
		$this->exist3D	= file_exists(PATH_TO_PROJECTS . $this->path . $this->extension3D);
	}

	public function createFileName() : string {
		return $this->fileName = $this->prefix . "." . $this->classifier . "." . $this->number . " " . $this->name;
	}

}

?>