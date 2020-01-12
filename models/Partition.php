<?php

class Partition extends Node {
	
	public function __construct(string $name, int $nodeType, int $parentId, int $quantity, int $ownerId, 
		string $ownerName, int $status, string $path, string $note, int $creationDate = -1,  int $id = -1) {
		
		parent::__construct($name, $nodeType, $parentId, $quantity, $ownerId, $ownerName, $status, $path, $note, $creationDate, $id);

		$this->fileName = $name;

	}

}

?>