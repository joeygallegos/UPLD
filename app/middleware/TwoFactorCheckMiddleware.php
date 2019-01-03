<?php
namespace App\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;
class TwoFactorCheckMiddleware {
	protected $container;
	const VERIFICATION_SESSION_NAME = 'token_verified';

	public function __construct($container) {
		$this->container = $container;
	}

	public function __invoke(Request $request, Response $response, callable $next) {
		$verified = isset($_SESSION[self::VERIFICATION_SESSION_NAME]) ? $_SESSION[self::VERIFICATION_SESSION_NAME] : false;
		if (!$verified) {
			// TODO: need tfa screen
			return $response->withRedirect($authorizedLogin->redirect_path);
		}

		return $next($request, $response);
	}
}