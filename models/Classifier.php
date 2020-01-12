<?php

class Classifier {
	
	public $number;
	public $name;
	public $emptyNumber;

	protected $db;

	private const DIGIT = 3;

	public function __construct(int $number, string $name, int $emptyNumber) {
		$this->number 		= $number;
		$this->name 		= $name;
		$this->emptyNumber 	= $emptyNumber;
		$this->db 			= DB::getConnection();
	}

	public static function getClassifier(int $number) : Classifier {
		$db = DB::getConnection();

		$result = $db->prepare("select * FROM classifier WHERE classfier_num = ?");
		$result->execute(array($number));

		$result->fetch();
		return new Classifier(
							$result['number'], 
							$result['name'],
							$result['empty_number']);
	}

	public static function getAllClassifiers() : Array {
		$db = DB::getConnection();

		$result = $db->prepare("select * FROM classifier ORDER BY number");
		$result->execute();

		$classifiers = array();
		foreach ($result->fetchAll() as $data) {
			$classifiers[] = new Classifier(
								$data['number'], 
								$data['name'],
								$data['empty_number']);
		}

		return $classifiers;
	}

	public function add() : bool {
		$result = $this->db->prepare("insert INTO `classifier`(`number`, `name`, `empty_number`) VALUES (?, ?, ?)");
		return $result->execute(array($this->number,
							   		  $this->name,
							   		  $this->emptyNumber
		));
	}

	public function update() : bool {
		$result = $this->db->prepare("update `classifier` SET `number`= ?,`name`= ?,`empty_number`= ? WHERE number = ?");
		return $result->execute(array($this->number,
							   		  $this->name,
							   		  $this->emptyNumber,
							   		  $this->number
		));
	}

	public function delete() : bool {
		$result = $this->db->prepare("delete FROM `classifier` WHERE number = ?");
		return $result->execute($this->number);
	}

	public static function increaseEmptyNumber(int $number) : bool {
		$db = DB::getConnection();

		$result = $db->prepare("update `classifier` SET `empty_number` = `empty_number` + 1 WHERE `number` = ?");
		return $result->execute(array($number));
	}

	public static function getEmptyNumber(int $number) : int {
		$db = DB::getConnection();

		$result = $db->prepare("select empty_number FROM `classifier` WHERE number = ?");
		$result->execute(array($number));

		$result = $result->fetch();
		return $result['empty_number'];
	}

	public static function isEmptyNumber(int $number, int $emptyNumber) : bool {
		if ($emptyNumber == 0)
			return false;
		
		$db = DB::getConnection();

		$result = $db->prepare("select COUNT(*) as num FROM classifier WHERE number = ? AND empty_number = ?");
		$result->execute(array($number,
								$emptyNumber
		));

		$result = $result->fetch();

		return $result['num'] == 0 ? true : false;
	}

	public static function isClassifierExists(int $number) : bool {
		$db = DB::getConnection();

		$result = $db->prepare("select COUNT(*) as num FROM classifier WHERE number = ?");
		$result->execute(array($number));
		
		$result = $result->fetch();

		return $result['num'] == 0 ? false : true;
	}

	public static function convertToLeadingZeros($num) : string {
		$numWithLeadingZeros = "";
		for ($i = 1; $i < Classifier::DIGIT; $i++) {
			if ($num / (10 ** $i) < 1)
				$numWithLeadingZeros .= '0';
		}
		return $numWithLeadingZeros . $num;
	}

}

?>