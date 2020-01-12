<?php

class Specification extends Node {

	private $positionCounter = 1;

	private $node;
	private $parentNode;
	private $documentationMask;

	private $documentation = array(
								'Сборочный чертёж',
								'Габаритный чертёж',
	); 

	private $specificationSections = array(
										'Сборочные еденицы',
										'Детали',
										'Стандартные изделия',
										'Прочие изделия',
										'Материалы'
	);

	private const MAX_MASK = 0b110;

	private const PATH_TO_SPECIFICATION_TEMPLATE 	= ROOT . '/template/specification_template.docx';
	private const PATH_TO_SAVE 						= "D:/";

	private const STR_LIMIT 		= 20;
	private const POSITION_OFFSET 	= 3;

	private const FORMAT 	= 'f';
	private const POSITION	= 'p';
	private const NUMBER 	= 'number';
	private const NAME 		= 'name';
	private const COUNT 	= 'c';
	private const NOTE 		= 'note';

	private const NODE_NUMBER 			= 'nodeNumber';
	private const NODE_NAME 			= 'nodeName';
	private const NODE_PARENT_NUMBER 	= 'nodeParentNumber';
	private const NODE_DEVELOPER 		= 'developer';

	public function __construct(string $prefix, int $classifier, string $number, string $name, string $suffix, int $nodeType, 
		int $quantity, int $ownerId, int $status = 0, string $path = '', string $note = '') {
		parent::__construct($prefix, $classifier, $number, $name, $suffix, $nodeType, 
			$quantity, $ownerId, $status, $path, $note);

		$this->extension = ".docx";
	}

	public static function getInstance(int $nodeId, $parentId, $mask = 0b11) : Specification {
		$node = Node::getNodeById($nodeId, $parentId, true);

		$specification = new Specification(
							$node->prefix,
							$node->number,
							$node->name,
							$node->suffix,
							999,
							1,
							$node->ownerId,
							0
		);
		$specification->parentId			= $parentId;
		$specification->node 				= $node;
		$specification->documentationMask 	= $mask;

		return $specification;
	}

	public function create() : void {
		
		/*
		$nodes[0][] <-- assemblies
		$nodes[1][] <-- details
		$nodes[2][] <-- standartParts
		$nodes[3][] <-- otherParts
		$nodes[4][] <-- materials
		*/
		$nodes = [array(), array(), array(), array(), array()];

		$materialsObject = array();

		foreach ($this->node->child as $child) {
			switch ($child->nodeType) {
			case Node::ASSEMBLY:
				$type = 0;
				break;

			case Node::DETAIL:
				$type = 1;

				foreach ($child->material as $material) {
					$material->quantity *= $child->quantity; 
					$materialsObject[] = $material;
				}
				break;

			case Node::STANDART_PARTS:
				$type = 2;
				break;

			case Node::OTHER_PARTS:
				$type = 3;
				break;
			
			default:
				break;
			}

			foreach ($this->createRows(array(
											SheetFormat::getSheetFormat($child->sheetFormat),
											$this->positionCounter,
											$child->number,
											$child->name,
											$child->quantity,
											$child->note
										), 0b111111) as $row) {
				$nodes[$type][] = $row;
			}
			$nodes[$type][] = $this->createRow();
		}

		if (!empty($materialsObject)) {
			foreach ($this->materialsSumUp($materialsObject) as $material) {
				foreach ($this->createRows(array(
												$material->name,
												$material->quantity
											), 0b000110) as $row) {
					$nodes[4][] = $row;	
				}
				$nodes[4][] = $this->createRow();
			}
		}
		
		$data = array();

		$data[] = $this->createRow("Документация");
		$data[] = $this->createRow();

		for ($i = 0; $i < count($this->documentation); $i++) {
			if ((0b1 & ($this->documentationMask >> $i)) == 1) {
				foreach ($this->createRows(array($this->documentation[$i]), 0b000100) as $row) {
					$data[] = $row;
				}
			}
		}

		$data[] = $this->createRow();
		$data[] = $this->createRow();

		for ($i = 0; $i < count($this->specificationSections); $i++) {
			if (!empty($nodes[$i])) {
				foreach ($this->createRows(array($this->specificationSections[$i]), 0b000100) as $row) {
					$data[] = $row;
				}
				$data[] = $this->createRow();

				foreach ($nodes[$i] as $elem) {
					$data[] = $elem;
				}

				$data[] = $this->createRow();
			}
		}

		$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(Specification::PATH_TO_SPECIFICATION_TEMPLATE);
/*
		$templateProcessor->cloneRow('name', 1);
		$name = new PhpOffice\PhpWord\Element\TextRun();
		$name->addText('My text', array('align' => 'both', 'name' => 'GOST Type B', 'size' => 14, 'italic' => true, 'underline' => 'single', 'vAlign' => 'qwerttyyu'));
		$templateProcessor->setComplexValue('name#1', $name);
*/
		$templateProcessor->cloneRowAndSetValues('name', $data);
		$templateProcessor->setValue(Specification::NODE_NUMBER, $this->node->number);
		$templateProcessor->setValue(Specification::NODE_NAME, $this->node->name);
		$templateProcessor->setValue(Specification::NODE_PARENT_NUMBER, $this->parentId == 0 ? '' : Node::getNodeById($this->parentId)->name);
		$templateProcessor->setValue(Specification::NODE_DEVELOPER, $this->node->ownerName);

		$templateProcessor->saveAs(PATH_TO_PROJECTS . dirname($this->node->path, 2) . DIRECTORY_SEPARATOR 
															. DOCUMENTATION . DIRECTORY_SEPARATOR . $this->node->fileName . $this->extension);
	}

	private function createRows(array $names = array(''), int $mask = 0b000000) : array {
		$row = array(
					Specification::FORMAT 	=> '',
					Specification::POSITION	=> '',
					Specification::NUMBER 	=> '',
					Specification::NAME 	=> '',
					Specification::COUNT 	=> '',
					Specification::NOTE 	=> ''
		);

		if (empty($names) || $mask == 0b000000)
			return array($row);

		$words = array();
		foreach ($names as $name) {
			$words[] = $this->splitWords($name);
		}

		$strings = array();
		for ($i = 0, $j = count($words) - 1; $i < Specification::MAX_MASK; $i++) {

			if ((($mask >> $i) & 0b1) == 1) {
				$strings[] = isset($words[$j]) ? $words[$j] : array('');
				$j--;
			}
			else {
				$strings[] = array('');
			}
		}
		$strings = array_reverse($strings);

		$maxLength = 0;
		foreach ($strings as $string) {
			$l = count($string);
			if ($l > $maxLength) 
				$maxLength = $l;
		}

		$keys = array_keys($row);
		for ($i = 0; $i < $maxLength; $i++) {
			$row = array();
			for ($j = 0; $j < Specification::MAX_MASK; $j++) {
				$row[$keys[$j]] = isset($strings[$j][$i]) ? $strings[$j][$i] : '';	
			}
			$rows[$i] = $row;
		}

		return $rows;
	}

	private function materialsSumUp(array $materials) : array {
		$newSet = array();
		for ($i = 0, $k = 0; $i < count($materials); $i++) {

			if (!is_null($materials[$i])) {
				
				$newSet[$k] = $materials[$i];
				for ($j = $i + 1; $j < count($materials); $j++) {
					if (!is_null($materials[$j]) && $newSet[$k]->matchingId == $materials[$j]->matchingId) {
						$newSet[$k]->quantity += $materials[$j]->quantity;
						$materials[$j] = null;
					}
				}

				$k++; 
			} // if
		} //for

		return $newSet;
	}

	private function splitWords(string $sentence) : array {
		if (mb_strlen($sentence, 'utf8') <= Specification::STR_LIMIT)
			return array($sentence);

		$matches = preg_split("~(\s+)~", $sentence);
		if (count($matches) < 2)
			return $matches;

		$words[0] = $matches[0];
		for ($i = 1, $str = 0; $i < count($matches); $i++) {

			$space = " ";
			if (mb_strlen($words[$str] . " " . $matches[$i], 'utf8') > Specification::STR_LIMIT) {
				$space = "";
				$words[++$str] = "";
			}
			$words[$str] .= $space . $matches[$i];
		}

		return $words;
	}



	/**
	 * 
	 * 
	 * ---------------------Depricated methods --------------------------
	 * 
	 * 
	 * 
	 */
	

	private function createRows2(Node $node) : array {

		$words = $this->splitWords($node->name);

		$rows[0] = array(
					Specification::FORMAT 	=> SheetFormat::getSheetFormat($node->sheetFormat),
					Specification::POSITION	=> $this->positionCounter,
					Specification::NUMBER 	=> $node->number,
					Specification::NAME 	=> $words[0],
					Specification::COUNT 	=> $node->quantity,
					Specification::NOTE 	=> $node->note
		);
		$this->positionCounter += Specification::POSITION_OFFSET;

		if (count($words) > 1) {
			for ($i = 1; $i < count($words); $i++) {
				$rows[] = $this->createNamedRow($words[$i]);
			}
		}
		
		return $rows;
	}

	private function createMaterialRows(Material $material) : array {
		$words = $this->splitWords($material->name);

		$rows[0] = array(
					Specification::FORMAT 	=> '',
					Specification::POSITION	=> '',
					Specification::NUMBER 	=> '',
					Specification::NAME 	=> $words[0],
					Specification::COUNT 	=> $material->quantity,
					Specification::NOTE 	=> ''
		);

		if (count($words) > 1) {
			for ($i = 1; $i < count($words); $i++) {
				$rows[] = $this->createNamedRow($words[$i]);
			}
		}
		
		return $rows;
	}

	private function createRow(string $name = '') : array {
		return array(
				Specification::FORMAT 	=> '',
				Specification::POSITION	=> '',
				Specification::NUMBER 	=> '',
				Specification::NAME 	=> $name,
				Specification::COUNT 	=> '',
				Specification::NOTE 	=> ''
		);
	}


}

?>