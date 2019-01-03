<?php
namespace App\Controllers;
use Slim\Http\Request;
use Slim\Http\Response;

class KeyController {
	protected $container;
	
	public function __construct($container) {
		$this->container = $container;
	}

	public function getUserKeys(Request $request, Response $response, $args) {
		// TODO: Landing page for the key provided to user
		
		$code = sanitize($args['code']);
		$requestAddress = '127.0.0.1';
		$uploadKey = UploadKey::where('code', '=', $code)->first();

		// if key not found and key != used
		if (!$uploadKey && $uploadKey->used != 0) {
			// TODO: Key not found in database
		}

		// check before upload

		// create new upload
		$upload = Upld::create([
			// TODO: Generate the hash for the app
			'hash' => $this->container->randomGenerator
		]);

		$updated = $uploadKey->update([
			'code' => null,
			'used' => 1,
			'address' => $requestAddress,
			'upld_id' => $upload->id
		]);

	}

	public function postUploadFile(Request $request, Response $response, $args) {

	}
}