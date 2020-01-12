<?php

class SheetFormat {

	public $id;
	public $name;

	protected static $formats = array('',
							'A0',
							'A1',
							'A2',
							'A3',
							'A4',
							'БЧ'
	);

	public function __construct(int $id) {
		$this->id = $id;
		$this->name = self::$formats[$id];
	}

	public static function getSheetFormats() : array {
		$sheetFormats = array();
		for ($i = 0; $i < count(self::$formats); $i++) {
			$sheetFormats[] = new SheetFormat($i);
		}
		return $sheetFormats;
	}

	public static function getSheetFormat(int $id) : string {
		return self::$formats[$id];
	}

}

?>