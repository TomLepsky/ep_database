<?php

class NodeException extends Exception {

	public function __construct(string $message = "", int $code = 1) {
		parent::__construct($message, $code);
	}
}

?>