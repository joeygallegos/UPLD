<?php
namespace App\Controllers;
use App\Helpers\Google2FA;
use App\Models\AccountCredential;
use App\Models\Authentication;
use App\Models\Sessions;
use App\Models\User;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Illuminate\Database\QueryException;
use Slim\Http\Request;
use Slim\Http\Response;
class GoogleAuthenticatorController {
	protected $container;
	
	const APP_NAME = 'UPLD';
	const VERIFICATION_WINDOW = 1;
	const VERIFICATION_SESSION_NAME = 'token_verified';
	const DISABLE_VERIFICATION_RESPONSE_IF_VERIFIED_ALREADY = true;

	public function __construct($container) {
		$this->container = $container;
	}

	public function tryAuthenticator(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		$authentication = $user->authentication;
		if (is_null($authentication) && is_null($authentication->auth_key)) {
			die('authentication model not set');
		}

		$verified = isset($_SESSION[self::VERIFICATION_SESSION_NAME]) ? $_SESSION[self::VERIFICATION_SESSION_NAME] : false;
		$tokenValid = Google2FA::verify_key($authentication->auth_key, sanitize($request->getParam('token')), self::VERIFICATION_WINDOW);
		if (!$verified) {
			$data = [
				'user_id' => $user->id,
				'token_valid' => $tokenValid
			];
			if ($tokenValid) {
				$_SESSION['token_verified'] = true;
			}
		}

		if (self::DISABLE_VERIFICATION_RESPONSE_IF_VERIFIED_ALREADY) {
			return $response->withHeader('Content-Type', 'application/json')->withJson([], 200, JSON_UNESCAPED_UNICODE);
		}

		return $response->withHeader('Content-Type', 'application/json')->withJson($data, 200, JSON_UNESCAPED_UNICODE);
	}
	public function getNewAuthenticator(Request $request, Response $response, $args) {
		if (!$user = Sessions::getUser()) {
			die('not auth');
		}

		// if user has authentication model
		$authentication = $user->authentication;
		if ($authentication != null && $authentication->auth_key != null) {

			// path for QR code
			$path = $this->getAuthenticationPath($user, $authentication->auth_key);

			// create QR code and encode
			$qr = $this->getQR($path);

			// write data to response
			return $this->getResponse($response, $qr);
		}

		// generate and update key since it doesn't exist
		$key = Google2FA::generate_secret_key();
		try {
			$created = Authentication::create([
				'user_id' => $user->id,
				'auth_key' => $key
			]);
		} catch (QueryException $e) {
			die($e->getMessage());
		}

		// path for QR code
		$path = $this->getAuthenticationPath($user, $key);

		// create QR code and encode
		$qr = $this->getQR($path);

		// write data to response
		return $this->getResponse($response, $qr);
	}

	private function getAuthenticationPath(User $user, string $key) {
		return "otpauth://totp/$user->username?secret=$key&issuer=" . self::APP_NAME;
	}

	private function getQR($string) {
		$qr = new QrCode($string);
		$qr->setSize(200);
		$qr->setMargin(3);
		$qr->setEncoding('UTF-8');
		$qr->setWriterByName('png');
		$qr->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
		return $qr;
	}

	private function getResponse(Response $response, QrCode $qr) {
		$response->getBody()->write($qr->writeString());
		return $response->withHeader('Content-Type', $qr->getContentType());
	}
}