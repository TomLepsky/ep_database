<?php

class Win64 extends System {
	
	public function copy(string $from, string $to) : void {
		exec('copy "' . $from . '" "' . $to . '"');
	}

	public function move(string $from, string $to) : void {
		exec('move /Y "' . $from . '" "' . $to . '"');
	}

	public function deleteFile(string $path) : void {
		exec('del /F /Q "' . $path . '"');
	}

	public function deleteFolder(string $path) : void {
		exec('rmdir /Q "' . $path . '"');
	}

	public function createFile(string $path, string $content) : void {
		exec('chcp 1251 & echo ' . $content . ' > "' . $path . '"');
	}

	public function createFolder(string $path) : void {
		exec('mkdir "' . $path . '"');
	}

	public function createLink(string $pathToObject, string $destinationPath) : void {
		exec('mklink "' . $destinationPath . '" "' . $pathToObject . '"');
	}

}

?>