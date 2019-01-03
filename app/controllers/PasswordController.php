<?php
namespace App\Controllers;
use App\Helpers\Google2FA;
use App\Models\AccountCredential;
use App\Models\Sessions;
use Illuminate\Database\QueryException;
use Slim\Http\Request;
use Slim\Http\Response;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
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
		$credentials = AccountCredential::where('user_id', '=', $user->id)->where('deleted', '!=', 1)->get();
		foreach ($credentials as $credential) {
			array_push($payload, $credential->toReadableJson());
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
			'password' => $this->container->randomGenerator->generateString($passwordLength, $chars)
		];
		return $response->withHeader('Content-Type', 'application/json')->withJson($data, 200, JSON_UNESCAPED_UNICODE);
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
			return $response->withHeader('Content-Type', 'application/json')->withJson($data, 401, JSON_UNESCAPED_UNICODE);
		}

		$data = [
			'response' => [
				'responseSuccess' => true,
				'message' => 'Please wait while we load your account data..',
				'object' => $account->toJson()
			]
		];
		return $response->withHeader('Content-Type', 'application/json')->withJson($data, 200, JSON_UNESCAPED_UNICODE);
	}
}