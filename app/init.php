<?php

use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler as WhoopsPrettyPageHandler;
use Illuminate\Database\Capsule\Manager as Capsule;
use Gears\Router as Router;
use Slim\App;
use Mailgun\Mailgun;

require_once '../vendor/autoload.php';
date_default_timezone_set('America/Chicago');
session_start();

// pretty error handling
$whoops = new WhoopsRun();
$handler = new WhoopsPrettyPageHandler();
$whoops->pushHandler($handler)->register();

// Eloquent Models
$capsule = new Capsule();
$capsule->addConnection([
	'driver' => 'mysql',
	'host' => 'joeygallegos.com',
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

// SCSS stylesheet cleanup code
$scss = new Leafo\ScssPhp\Compiler;

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
	if (startsWith($file->getBasename('.scss'), '_')) continue;

	$in = file_get_contents($inDir . '/' . $file->getFilename());
	$out = $scss->compile($in);
	file_put_contents($outDir . '/' . $file->getBasename('.scss') . '.css', $out);
}

define('ENVIRONMENT', 'local');

// environment settings
$slimSettings = [];
$slimSettings['addContentLengthHeader'] = false;

// if testing environment
if (ENVIRONMENT == 'local' || ENVIRONMENT == 'staging') {
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
	ini_set('display_startup_errors', 'On');
	$slimSettings['displayErrorDetails'] = true;
	$slimSettings['debug'] = true;
	$slimSettings['whoops.editor'] = 'sublime';
}
else {
	error_reporting(0);
	ini_set('display_errors', 'Off');
	ini_set('display_startup_errors', 'Off');
	ini_set("error_log", "/var/www/production/logs/php-errors.log");
}

$app = new App(['settings' => $slimSettings]);
$c = $app->getContainer();

$urlAuth = false;
include_once 'routes.php';

$app->run();