<?php

class View {
	public static function make($view = null, $params = array()) {

		$view = str_replace('.', '/', $view);
		$base = dirname(__DIR__);

		extract($params);
		ob_start();

		include_once $base . '/layout/top.php';

		// View
		if (!is_null($view)) {
			include_once $base . "/views/${view}.php";
		}
		
		include_once $base . '/layout/bottom.php';
	}

	public static function current() {
		return parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
	}
}