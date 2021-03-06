<?php
namespace App\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;
class PreserveInputMiddleware {
	protected $container;
	const VERIFICATION_SESSION_NAME = 'token_verified';

	public function __construct($container) {
		$this->container = $container;
	}

	public function __invoke(Request $request, Response $response, callable $next) {
		if (isset($_SESSION['old_params'])) {
			$this->container->view->getEnvironment()->addGlobal('old_params', $_SESSION['old_params']);
		}

		$_SESSION['old_params'] = $request->getParams();
		return $next($request, $response);
	}
}