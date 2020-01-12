<?php

abstract class Controller {
	
	protected $system;

	public $message;
	public $info = false;
	public $title = "";
	public $options = array(
					'nodelabel' 	=> false,
					'nodetable' 	=> false,
					'detail'		=> false,
					'outputinfo'	=> false,
					'editnode' 		=> false,
					'addnewnode' 	=> false,
					'nodecopy' 		=> false
	);

	public function __construct() {
		$this->system = ENVIRONMENT == 'Win64' ? new Win64() : new Unix();
	}

	protected function createFile($file, $content = "test") {
		if(!file_exists($file)) {
		$fp = fopen($file, "w"); 
		fwrite($fp, $content);
		fclose ($fp);
		}
	}

	protected function createAssemblyFolders(string $path) : bool {
		if (!file_exists($path)) {
			if (!mkdir($path))
				return false;
		}

		$folders = array(
					DOCUMENTATION,
					ASSEMBLIES,
					DETAILS,
					TABLES,
					CANCELED
		);

		$path .= DIRECTORY_SEPARATOR;
		foreach ($folders as $folder) {
			if (!file_exists($path . $folder)) {
				if (!mkdir($path . $folder)) {
					$this->rmrf($path);
					return false;
				}
			}	
		}

		return true;
	}

	protected function createDefaultFolders(string $path) : void {
		$folders = array(
					DOCUMENTATION,
					ASSEMBLIES,
					DETAILS,
					TABLES,
					CANCELED
		);

		foreach ($folders as $folder) {
			if (!file_exists($path . $folder))
				$this->system->createFolder($path . $folder);
		}
	}

	protected function rmrf($path) : void {
		if (is_dir($path)) {
			$objects = scandir($path);

			foreach ($objects as $obj) {
				if ($obj != "." && $obj != "..") {

					if (is_dir($path . DIRECTORY_SEPARATOR . $obj)) 
						$this->rmrf($path . DIRECTORY_SEPARATOR . $obj);
					else
						unlink($path . DIRECTORY_SEPARATOR . $obj);
				}

			}
			rmdir($path); 
		}
	}

	protected function renamePath($oldPath, $newPath, $returnPath = true) : bool {
		preg_match_all("~([^\\\/]+)~", $oldPath, $oldMatches);
		preg_match_all("~([^\\\/]+)~", $newPath, $newMatches);
		
		if (count($oldMatches) != count($newMatches))
			return false;

		$np = $oldMatches[0][0];
		$op = $oldMatches[0][0];

		for ($i = 1; $i < count($oldMatches[0]); $i++) {
			$op .= DIRECTORY_SEPARATOR . $oldMatches[0][$i];
			$np .= DIRECTORY_SEPARATOR . $newMatches[0][$i];

			if (strcasecmp($op, $np) == 0)
				continue;

			if (!rename($op, $np)) {
				if($returnPath) {
					$this->renamePath($newPath, $oldPath, false);	
				}
				return false;
			}
			$op = $np;
		}
		return true;
	}

} 

?>