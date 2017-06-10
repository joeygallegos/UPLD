<?php

	use Whoops\Run as WhoopsRun;
	use Whoops\Handler\PrettyPageHandler as WhoopsPrettyPageHandler;
	use Illuminate\Database\Capsule\Manager as Capsule;
	use Gears\Router as Router;
	use PHPEncryptData\Simple as Encryptor;
	use Mailgun\Mailgun;

	require_once '../vendor/autoload.php';

	// pretty error handling
	$whoops = new WhoopsRun();
	$handler = new WhoopsPrettyPageHandler();
	$whoops->pushHandler($handler)->register();

	$codes = array(
		'100' => 'Continue',
		'200' => 'OK',
		'201' => 'Created',
		'202' => 'Accepted',
		'203' => 'Non-Authoritative Information',
		'204' => 'No Content',
		'205' => 'Reset Content',
		'206' => 'Partial Content',
		'300' => 'Multiple Choices',
		'301' => 'Moved Permanently',
		'302' => 'Found',
		'303' => 'See Other',
		'304' => 'Not Modified',
		'305' => 'Use Proxy',
		'307' => 'Temporary Redirect',
		'400' => 'Bad Request',
		'401' => 'Unauthorized',
		'402' => 'Payment Required',
		'403' => 'Forbidden',
		'404' => 'Not Found',
		'405' => 'Method Not Allowed',
		'406' => 'Not Acceptable',
		'409' => 'Conflict',
		'410' => 'Gone',
		'411' => 'Length Required',
		'412' => 'Precondition Failed',
		'413' => 'Request Entity Too Large',
		'414' => 'Request-URI Too Long',
		'415' => 'Unsupported Media Type',
		'416' => 'Requested Range Not Satisfiable',
		'417' => 'Expectation Failed',
		'500' => 'Internal Server Error',
		'501' => 'Not Implemented',
		'503' => 'Service Unavailable'
	);

	// Eloquent Models
	$capsule = new Capsule();
	$capsule->addConnection([
		'driver' => 'mysql',
		'host' => 'localhost',
		'port' => '3306',
		'database' => 'upld',
		'username' => 'root',
		'password' => '4ZHKnQUj&',
		'charset' => 'utf8',
		'collation' => 'utf8_unicode_ci',
		'prefix' => ''
	]);
	$capsule->setFetchMode(PDO::FETCH_OBJ);
	$capsule->setAsGlobal();
	$capsule->bootEloquent();

	$mailgun = new Mailgun('');
	$domain = "";

	// Encryption data here
	$encryptionKey = '';
	$macKey = '';
	$phpcrypt = new Encryptor($encryptionKey, $macKey);

	// SCSS stylesheet cleanup code
	$scss = new scssc();

	$inDir = dirname(__DIR__) . '/public/assets/scss';
	$outDir = dirname(__DIR__) . '/public/assets/css';

	if (!file_exists($outDir)) {
		mkdir($outDir, 0777, true);
	}

	// Weather
	$weather = new Weather(Config::get('weather_key', null));

	$files = scandir($inDir);
	foreach(new DirectoryIterator($inDir) as $file) {
		if ($file->isDot()) continue;
		if ($file->getExtension() !== 'scss') continue;
		// TODO: Fix this continue being invalid
		if (startsWith($file->getBasename('.scss'), '_')) continue;

		$in = file_get_contents($inDir . '/' . $file->getFilename());
		$out = $scss->compile($in);
		file_put_contents($outDir . '/' . $file->getBasename('.scss') . '.css', $out);
	}

	$app = new \Slim\Slim();
	$app->add(new \Slim\Middleware\SessionCookie(array('secret' => '')));

	$app->config('debug', true);
	$app->setName('UPLD');

	$app->container->singleton('db', function() {
		return $capsule->getConnection();
	});

	$urlAuth = false;
	include_once 'routes.php';
	$app->run();
