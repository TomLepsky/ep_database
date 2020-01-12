<?php

Abstract class System {
	
	abstract public function copy(string $from, string $to) : void;

	abstract public function move(string $from, string $to) : void;

	abstract public function deleteFile(string $path) : void;

	abstract public function deleteFolder(string $path) : void;

	abstract public function createFile(string $path, string $content) : void;

	abstract public function createFolder(string $path) : void;

	abstract public function createLink(string $pathToObject, string $destinationPath) : void;
}

?>