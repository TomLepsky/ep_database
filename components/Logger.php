<?php

class Logger {

	public $var;
	public $title;

	public function __construct($var, string $title = '') {
		$this->var = $var;
		$this->title = $title;
	}

	public static function consoleLog ($var, $title = 'PHP-log') : void {
    $str = json_encode(print_r ($var, true));
    echo "
        <script>
            console.group('" . $title . "');
            console.log('" . $str . "');
            console.groupEnd();
        </script>";
	}

	public static function print() : void {
		self::consoleLog($info);
		echo "<pre>";
		print_r($_SERVER['HTTP_HOST']);
		echo "</pre>";
		exit();
	}
}

?>