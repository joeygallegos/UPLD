<?php

namespace App\Controllers;
use App\Helpers\Google2FA;
use App\Models\AccountCredential;
use App\Models\Sessions;
use App\Models\User;
use Carbon\Carbon;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Slim\Http\Request;
use Slim\Http\Response;
class PasswordController {
	protected $container;

	const CHAR_UPPER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	const CHAR_LOWER = 'abcdefghijklmnopqrstuvwxyz';
	const CHAR_DIGITS = '0123456789';
	const CHAR_SYMBOLS = '!"#$%&\'()* +,-./:;<=>?@[\]^_`{|}~';

	public function __construct($container) {
		$this->container = $container;
	}

	public function getPasswordManager(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		// download user credentials
		$payload = [];
		$credentials = AccountCredential::where([
			['user_id', '=', $user->id],
			['deleted_at', '=', null]
		])->get();
		foreach ($credentials as $credential) {
			array_push($payload, $credential->toHydrationArray());
		}

		// show password manager page
		return $this->container->view->render($response, '/templates/password-manager.twig', [
			'user' => $user,
			'credentials' => $payload,
			'title' => 'Password Manager - UPLD',
			'styles' => [
				'reset',
				'grid',
				'admin'
			],
			'scripts' => [
				'tippy',
				'passwords'
			]
		]);
	}

	public function getPasswordString(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		$passwordLength = 15;
		$chars = self::CHAR_LOWER . self::CHAR_UPPER . self::CHAR_DIGITS;

		$includeSpecial = intval($request->getParam('special'));
		$requestedLength = intval($request->getParam('length'));
		if ($includeSpecial != 0) {
			$chars .= self::CHAR_SYMBOLS;
		}

		if ($requestedLength <= 100) {
			$passwordLength = $requestedLength;
		}

		$data = [
			'response' => [
				'success' => true,
				'password' => $this->container->randomGenerator->generateString($passwordLength, $chars)
			]
		];
		return $this->setJsonResponse($response, $data, 200);
	}

	public function updatePassword(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		$hash = sanitize($request->getParam('hash'));
		$password = $request->getParam('password');

		$acct = AccountCredential::where('hash', '=', $hash)->first();
		$encryptionData = openEncrypt($password);

		$updated = $acct->update([
			'enc_password' => $encryptionData['string'],
			'enc_method' => $encryptionData['method'],
			'enc_key' => $encryptionData['key'],
			'enc_iv' => $encryptionData['iv'],
			'password_changed_at' => Carbon::now()
		]);

		$data = [
			'response' => [
				'success' => $updated
			]
		];

		return $this->setJsonResponse($response, $data, 200);
	}

	public function createAccountCredentials(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		$nickname = sanitize($request->getParam('nickname'));
		$loginid = sanitize($request->getParam('loginid'));
		$password = $request->getParam('password');

		$loginlink = $request->getParam('loginlink');

		$encryptionData = openEncrypt($password);

		try {
			$account = AccountCredential::create([
				'user_id' => $user->id,
				'nickname' => $nickname,
				'loginid' => $loginid,

				'enc_password' => $encryptionData['string'],
				'enc_method' => $encryptionData['method'],
				'enc_key' => $encryptionData['key'],
				'enc_iv' => $encryptionData['iv'],

				'loginlink' => $loginlink
			]);
		} catch (QueryException $e) {
			$data = [
				'response' => [
					'responseSuccess' => false,
					'message' => 'Error thrown while creating the credentials..',
					'exception' => $e->getMessage()
				]
			];
			return $this->setJsonResponse($request, $data, 400);
		}

		$data = [
			'response' => [
				'responseSuccess' => true,
				'message' => 'Please wait while we load your account data..',
				'object' => $account->toJson()
			]
		];
		return $response->setJsonResponse($response, $data, 200);
	}

	public function getCredentials(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		// download user credentials
		$payload = null;
		try {
			$payload = $this->getCredentialsCollection($user);
		} catch (QueryException $e) {
			$data = [
				'response' => [
					'success' => false,
					'error' => $e->getMessage()
				]
			];
			return $this->setJsonResponse($response, $data, 400);
		}

		return $this->setJsonResponse($response, $payload, 200);
	}

	protected function getCredentialsCollection(User $user) {
		$credentials = AccountCredential::where([
			['user_id', '=', $user->id],
			['deleted_at', '=', null]
		])->get();

		$collection = new Collection;
		foreach ($credentials as $credential) {
			$collection->add($credential->toHydrationArray());
		}
		return $collection;
	}

	protected function setJsonResponse(Response $response, $payload, $status = 200) {
		return $response->withHeader('Content-Type', 'application/json')->withJson($payload, $status, JSON_UNESCAPED_UNICODE);
	}
}