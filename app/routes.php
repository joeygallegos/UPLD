<?php
	$app->hook('slim.before.dispatch', function() use ($app) {
		$user = null;
		if (isset($_SESSION['user'])) {
			$user = $_SESSION['user'];
		}
		$app->view()->setData('user', $user);

		// Create a unique session token
		// Error, token always different
		$token = null;
		if (isset($_SESSION['token'])) {
			$token = $_SESSION['token'];
		}
		else {
			$token = md5(uniqid(rand(), TRUE));
			$_SESSION['token'] = $token;
		}
		$app->view()->setData('token', $token);
	});

	$app->get('/', function() use ($app) {
		if (Sessions::getUser()) {
			Controller::run('CoreController@getDashboard', array('app' => $app, 'user' => Sessions::getUser()));
		} else {
			Controller::run('CoreController@index');
		}
	});

	$app->get('/update/password/:id/:password', function($id = 0, $password = '') use ($app) {
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
			echo "No user found with this ID";
		}
	});

	$app->get('/playground/', function() use ($app) {
		if (isset($_SESSION['user'])) {
			$user = User::where($_SESSION['user'])->first();

			$customer = Customer::where('customer_id', '=', '1')->first();
			$primary = Contact::where('id', '=', $customer->main_contact_id)->first();

			$data = [
				'customer' => $customer,
				'primary_contact' => $primary
			];
			echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
		}
		else {
			echo "No active user found";
		}
	});

	$app->get('/dashboard/data/', function() use ($app)  {
		if (isset($_SESSION['user']) && !(is_null($_SESSION['user']))) {
			$user = User::where($_SESSION['user'])->first();
			if ($user) {

				$status = $user->status ? 'Active' : 'Inactive';
				$data = [
					'user_id' => $user->id,
					'username' => $user->username,
					'user_alias' => $user->alias->alias_name,
					'user_status' => $status,
					'user_token' => $_SESSION['token'],
					'token_x' => Utils::random(12),
					'user_data' => $user
				];

				echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
			}
			else {
				echo "No active account found";
			}
		} else {
			echo "No logged in user";
		}
	});

	$app->post('/ajax/login/', function() use ($app) {
		if ($app->request->isAjax()) {
			$action = $app->request->post('action');
			$username = $app->request->post('username');
			$password = $app->request->post('password');

			if ($action == 'login') {
				$user = User::where('username', $username)->first();
				if ($user) {
					if (PassHash::check_password($user->password, $password)) {
						$_SESSION['user'] = $user;
						jsonify(
							array(
								'response' => array(
									'code' => 1,
									'message' => 'Success'
							)
						), true);
					}
					else {
						jsonify(
							array(
								'response' => array(
									'code' => 0,
									'message' => 'That password seems incorrect!'
							)
						), true);
					}
				}
				else {
					jsonify(
						array(
							'response' => array(
								'code' => 0,
								'message' => 'That user doesn\'t seem to exist!'
						)
					), true);
				}
			}
		}
	});

	$app->post('/ajax/upload/', function() use ($app, $mailgun, $domain) {
		if ($app->request->isAjax()) {
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

						$didEmail = $mailgun->sendMessage($domain,
							array(
								'from' => 'UPLD CXNTR <postmaster@sandbox2dbdc5bf677240bebd16ccd00e2cc2a9.mailgun.org>',
								'to' => 'Joey <joey@joeygallegos.com>',
								'subject' => 'UPLD Update',
								'html' => $content
							)
						);

						if ($didEmail) {
							jsonify(array(
								'response' => 1,
								'message' => 'Your file has been uploaded and an alert has been sent. Your file has been uploaded at the link below: <br>http://upld.joeygallegos.com/up/' . $newFile,
								'upld' => $upld,
							), true);
						}
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

	// Only if logged in and only if ajax call
	// Do a check on the sessions user id and the one we're requesting
	$app->get('/re-issue/', function() use ($app) {
		$posts = Post::all();
		foreach ($posts as $post) {
			$post->key = md5(uniqid(rand(), TRUE));
			$post->save();
		}
		var_dump($posts);
	});

	$app->get('/ajax/api/:id', function($id) use ($app, $weather) {
		setHeader();
		jsonify(array(
			'response' => 1,
			'weather' => array('temp' => 20),
			'uploads' => Upld::where('user_id', '=', $id)->get(),
			'upload_keys' => []
		), true);

		die();
		$balance = 0;
		if ($app->request->isAjax()) {
			if (!is_null(Sessions::getUser())) {
				if (Sessions::isSafe($id)) {
					$posts = Post::where('belongs_to', '=', $id)->get();
					$uploads = Upld::where('belongs_to', '=', $id)->get();
					$keys = UploadKey::where('belongs_to', '=', $id)->get();

					// If we find a post without a key, give it one
					foreach ($posts as $post) {
						if ($post->key == '') {
							$post->key = md5(uniqid(rand(), TRUE));
							$post->save();
						}
					}

					setHeader();
					jsonify(array(
						'response' => 1,
						'posts' => $posts,
						'uploads' => $uploads,
						'bankBalance' => $balance,
						'weather' => 4,//$weather->grab(),
						'upload_keys' => $keys
					), true);
				} else {
					setHeader();
					jsonify(array(
						'response' => array(
							'code' => 0,
							'message' => 'Not Authorized'
						)
					), true);
				}
			}
		}
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
