<?php
use App\Controllers\DashboardController;
use App\Controllers\GoogleAuthenticatorController;
use App\Controllers\LoginController;
use App\Controllers\PasswordController;
use App\Models\PassHash;
use App\Models\Sessions;
use App\Models\User;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

$app->get('/', DashboardController::class . ':getUserDashboard');
$app->get('/dashboard/hydrate/', DashboardController::class . ':getHydrationData');

$app->post('/ajax/login/', LoginController::class . ':postLoginUser');

// password manager
$app->get('/passman/', PasswordController::class . ':getPasswordManager');
$app->post('/passman/create/', PasswordController::class . ':createAccountCredentials');
$app->get('/passman/new/', PasswordController::class . ':getPasswordString');

// generate authenticator image
$app->get('/passman/image/', GoogleAuthenticatorController::class . ':getNewAuthenticator');
$app->get('/passman/authenticate/', GoogleAuthenticatorController::class . ':tryAuthenticator');

$app->get('/update/password/{id}/{password}', function($request, $response, $args) use ($app) {
	$id = $args['id'];
	$password = $args['password'];

	$user = User::where(array('id' => $id, 'active' => 1))->first();
	if ($user) {
		if (mb_strlen($password) > 0) {
			$user->password = PassHash::hash($password);
			$updated = $user->update();
			if ($updated) {
				echo "This account has been updated";
			}
		}
		else {
			echo "Password not provided";
		}
	}
	else {
		echo "No active user with this User ID found";
	}
});




	$app->post('/ajax/upload/', function($request, $response, $args) use ($app) {
		if ($request->isXhr()) {
			if (!isset($_FILES['upload'])) {
				setHeader();
				jsonify(array(
					'response' => array(
						'code' => 0,
						'message' => 'No files were attached to request'
					)
				), true);
			}
			else {
				$file = $_FILES['upload'];
				if ($file['error'] === UPLOAD_ERR_OK) {

					$fileName = strtolower($file['name']);
					$fileExtension = substr($fileName, strrpos($fileName, '.'));
					$hash = Utils::random(10);

					// TODO: Check for already used ID
					$newFile = $hash.$fileExtension;
					$upld = Upld::create(array(
						'user_id' => 1, // TODO: Update this
						'hash' => $hash,
						'extension' => str_replace('.', '', $fileExtension),
					));

					$directory = dirname(dirname(__FILE__)) . '/public/up/';
					if(!is_dir($directory)) {
						mkdir($directory);
					}

					if(move_uploaded_file($file['tmp_name'], $directory.$newFile)) {
						$message = 'Somebody, if not you has posted a new update to UPLD. Please review the post!';
						$content = "<table width=\"100%\" height=\"100%\"><tbody><tr valign=\"center\"><td align=\"center\" style=\"display:block\">";
						$content .= "<a href=\"http://upld.joeygallegos.com/\" style=\"display:block;text-decoration:none;margin:50px 0px;width:36px;min-height:36px;line-height:36px;text-align:center;color:#fff;font-size:18px;font-weight:600;background-color:#5890ff;border-radius:18px\" target=\"_blank\">j</a>";
						$content .= "<p style=\"display:block;text-align:left;color:#b6b6b6;width:100%;max-width:470px;line-height:30px;font-size:16px;margin-bottom:60px\"><strong style=\"color:#5f5f5f;font-weight:600\">Dear Human,</strong><br><br>" . $message . "<br><br><a href=\"" . 'http://upld.joeygallegos.com/up/' . $newFile . "\" style=\"display:inline-block;padding:20px 0px;margin-bottom:30px;border-radius:3px;background-color:transparent;border:1px solid #5890ff;font-size:16px;text-align:center;font-weight:500;text-decoration:none;color:#5890ff;width:100%;max-width:470px\" target=\"_blank\">View on UPLD</a>";
						$content .= "</td></tr></tbody></table>";

						// TODO: Use EmailEngine
						// $didEmail = $mailgun->sendMessage($domain,
						// 	array(
						// 		'from' => 'UPLD CXNTR <postmaster@sandbox2dbdc5bf677240bebd16ccd00e2cc2a9.mailgun.org>',
						// 		'to' => 'Joey <joey@joeygallegos.com>',
						// 		'subject' => 'UPLD Update',
						// 		'html' => $content
						// 	)
						// );

						// if ($didEmail) {
						// 	jsonify(array(
						// 		'response' => 1,
						// 		'message' => 'Your file has been uploaded and an alert has been sent. Your file has been uploaded at the link below: <br>http://upld.joeygallegos.com/up/' . $newFile,
						// 		'upld' => $upld,
						// 	), true);
						// }
					}
					else {
						jsonify(array(
							'response' => 0,
							'message' => 'Error uploading file to the system'
						), true);
					}
				}
				else {
					jsonify(array(
							'response' => 0,
							'message' => 'Error uploading file ERROR UP OK ' . $file['error']
						), true);
					// TODO: ALWAYS GETS HERE
				}
			}
		}
		else {
			echo 'not json';
		}
	});

$app->get('/ajax/api/', function($request, $response, $args) use ($app) {
	$id = $request->getParam('id');

	if (!is_numeric($id) || $id <= 0 || is_null($id)) {
		$data = [
			'response' => [
				'responseSuccess' => false,
				'message' => 'This User ID is not valid.'
			]
		];
		return $response->withJson($data)->withStatus(200);
	}

	$uploads = Upld::where('user_id', '=', $id)->get();
	if (is_null($uploads)) {
		$uploads = [];
	}

	$data = [
		'response' => [
			'responseSuccess' => true,
			'weather' => array('temp' => 20),
			'uploads' => $uploads,
			'upload_keys' => []
		]
	];

	return $response->withJson($data)->withStatus(200);
	
});

	$app->get('/shared/uploading/:code', function($code) use ($app) {
		$uploadKey = UploadKey::where('code', '=', $code)->first();
		if ($uploadKey && $uploadKey->used == 0) {
			$address = $app->request->getIp();
			setHeader();
			jsonify($uploadKey);
			// Store the file ID that was uploaded
			// Clear the key from DB
			// Store IP that was used to store
		}
		else {
			setHeader();
			jsonify(array(
				'response' => array(
					'code' => 0,
					'message' => 'Key not found'
				)
			), true);
		}
	});

	$app->post('/shared/uploading/:request', function($request) use ($app) {
		if (!is_null($request)) {
			if ($request == 'new') {
				UploadKey::create(array());
			}
			else {
				setHeader();
				jsonify(array(
					'response' => array(
						'code' => 0,
						'message' => 'Request not found'
					)
				), true);
			}
		}
	});
