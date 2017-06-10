<?php
class Controller {

	public static function run($controller = null, $with = []) {
		if (is_null($controller)) {
			die('Null controller');
		}

		// TODO: Get file seperated by a hashtag and the method to run
		$controller = explode('@', $controller);
		$file = $controller[0];
		$method = $controller[1];

		//$with = array('app', Slim::getInstance());
		die(call_user_func_array([$file, $method], $with));
	}

}