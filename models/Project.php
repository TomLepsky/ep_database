<?php

class Project extends Assembly {

	public function __construct(string $prefix, int $classifier, string $number, string $name, string $suffix, int $nodeType, 
		int $quantity = 1, int $ownerId, int $status = 0, int $version = 0, string $path = '', string $note = '') {
		parent::__construct($prefix, $classifier, $number, $name, $suffix, $nodeType, 
			$quantity, $ownerId, $status, $version, $path, $note);

		//$this->fileName = preg_replace("~(СБ|сб|Сб|сБ)$~", "", $this->fileName);
	}

	/*
	public static function convertJson(array $data, $parentId = "#") : string {
		$jsonObj = "";

		foreach ($data as $elem) {
			$jsonObj .= '{ "id" : ' . $elem->id . ', "parent" : ' . $parentId . 
							', "text" : ' . $elem->prefix . $elem->number . 
											$elem->name . $elem->suffix . '},'; 
			if (!(is_null($elem->child)))
				$jsonObj .= self::convertJson($elem->child, $elem->id);
		}
		return $jsonObj;
	}

	public static function showTree(array $node) : string {
		$str = '<ul class="listNode">';
		foreach ($node as $elem) {
			$str .= is_null($elem->child) ? '<li class="list">' : '<li>';
			$str .= '<div class="node">';
			$str .=	'<a class="click-tree" href="/node/' . $elem->id . '/' . $elem->quantity . '/' . $elem->parentId . '">' . 
						$elem->fileName . '</a>';
			$str .= '</div>';
			$str .= '</li>';
			if (is_array($elem->child)) 
				$str .= Project::showTree($elem->child);
		}
		$str .= "</ul>";
		return $str;
	}
	*/

}

?>