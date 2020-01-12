<?php

class Status {
	
	public $id;
	public $name;
	private static $data = array('статус1',
							'статус2',
							'статус3',		
						);

	public function __construct(int $id, string $name) {
		$this->id = $id;
		$this->name = $name;
	}

	public static function getStatuses() : array {
		$statuses = array();
		for ($i = 0; $i < count(self::$data); $i++) {
			$statuses[] = new Status($i, self::$data[$i]);
		}
		return $statuses;
	}

	public static function getStatus($statusId) : string {
		$statusId = intval($statusId);
		return self::$data[$statusId];
	}
}

?>