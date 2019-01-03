<?php
namespace App\Controllers;
use App\Models\PassHash;
use App\Models\Sessions;
use App\Models\Upld;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Slim\Http\Request;
use Slim\Http\Response;

class DashboardController {
	protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}

	public function getUserDashboard(Request $request, Response $response, $args) {

		// user not logged-in
		if (!$user = Sessions::getUser()) {
			return $this->container->view->render($response, '/templates/login.twig', [
				'title' => 'Login - UPLD',
				'styles' => [
					'clean',
					'form-reset',
					'style'
				],
				'scripts' => [
					'timeago',
					'autosave',
					'masonry',
					'form',
					'countup',
					'front'
				],
				'tags' => [
					'login',
					'home'
				]
			]);
		}

		// show user dashboard
		return $this->container->view->render($response, '/templates/dashboard.twig', [
			'user' => $user,
			'title' => 'Dashboard - UPLD',
			'styles' => [
				'clean',
				'admin'
			],
			'scripts' => [
				'timeago',
				'autosave',
				'masonry',
				'form',
				'countup',
				'dashboard'
			]
		]);
	}

	public function getHydrationData(Request $request, Response $response, $args) {
		$action = sanitize($request->getParam('action'));
		$user = User::where('id', '=', $_SESSION['user']->id)->first();
		$data = [];

		switch ($action) {

			case 'uploads':
			$data = $this->getUploadsData($user);
				break;

			case 'user':
			$data = $this->getUserData($user);
				break;

			case 'weather':
			$data = $this->getWeatherData($user);
				break;
			
			case 'keys':
			$data = $this->getUserUploadKeys($user);
				break;
			
			default:
			// TODO: die app
				break;
		}
		return $response->withHeader('Content-Type', 'application/json')->withJson($data, 200, JSON_UNESCAPED_UNICODE);
	}

	// TODO: Get actual temp from backend
	public function getWeatherData($user) {
		return [
			'response' => [
				'responseSuccess' => true,
				'weather' => [
					'temp' => 20
				]
			]
		];
	}

	/**
	 * Get UploadKeys that are not used
	 * @param  [type] $user [description]
	 * @return [type]       [description]
	 */
	public function getUserUploadKeys($user) {
		$uploadKeys = UploadKey::where('used', '!=', 1)->get();

		// default option if no data
		if (is_null($uploadKeys)) {
			$uploadKeys = [];
		}

		// return payload
		return [
			'response' => [
				'responseSuccess' => true,
				'upload_keys' => $uploadKeys
			]
		];
	}
	
	public function getUploadsData($user) {
		$uploads = Upld::where('user_id', '=', $user->id)->get();

		// default option if no data
		if (is_null($uploads)) {
			$uploads = [];
		}

		// return payload
		return [
			'response' => [
				'responseSuccess' => true,
				'uploads' => $uploads,
				'upload_keys' => []
			]
		];
	}

	public function getUserData($user) {
		return [
			'user_id' => $user->id,
			'username' => $user->username,
			'user_alias' => $user->alias->alias_name,
			'user_data' => $user
		];
	}
}