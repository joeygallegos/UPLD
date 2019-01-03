<?php
namespace App\Controllers;
use App\Models\PassHash;
use App\Models\User;
use Carbon\Carbon;
use Slim\Http\Request;
use Slim\Http\Response;

class LoginController {
	protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}

	public function postLoginUser(Request $request, Response $response, $args) {
		$action = sanitize($request->getParam('action'));
		$username = sanitize($request->getParam('username'));
		$password = $request->getParam('password');

		if ($action != 'login') {
			return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
		}

		$user = User::where('username', '=', $username)->first();

		if (!$user) {
			$data = [
				'response' => [
					'responseSuccess' => false,
					'message' => 'We did not find an account with that username..'
				]
			];
			return $response->withHeader('Content-Type', 'application/json')->withJson($data, 401, JSON_UNESCAPED_UNICODE);
		}

		if (is_null($user->password)) {
			$data = [
				'response' => [
					'responseSuccess' => false,
					'message' => 'Your account does not seem to be properly setup..'
				]
			];
			return $response->withHeader('Content-Type', 'application/json')->withJson($data, 401, JSON_UNESCAPED_UNICODE);
		}


		if (!PassHash::check_password($user->password, $password)) {
			// TODO: send proper response for invalid credentials
			$data = [
				'response' => [
					'responseSuccess' => false,
					'message' => 'Your credentials seem to be invalid..'
				]
			];
			return $response->withHeader('Content-Type', 'application/json')->withJson($data, 401, JSON_UNESCAPED_UNICODE);
		}

		// TODO: Better way to set logged-in user?
		$_SESSION['user'] = $user;
		$data = [
			'response' => [
				'responseSuccess' => true,
				'message' => 'Please wait while we load your account data..'
			]
		];
		return $response->withHeader('Content-Type', 'application/json')->withJson($data, 200, JSON_UNESCAPED_UNICODE);
	}
}