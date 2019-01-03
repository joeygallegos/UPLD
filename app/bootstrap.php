<?php
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Database\Capsule\Manager as Capsule;

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
date_default_timezone_set('America/Chicago');
session_start();

try {
	$config = (new Dotenv(dirname(__FILE__) . '/env/', 'config.env'))->load();
}
catch (InvalidPathException $e) {
	die(); $e->getMessage();
}

/**
 * Create the database capsule
 * @var Capsule
 */
$capsule = new Capsule();
$capsule->addConnection([
	'driver' => getenv('DRIVER'),
	'host' => getenv('HOST'),
	'database' => getenv('DB_NAME'),
	'username' => getenv('DB_USERNAME'),
	'password' => getenv('DB_PASSWORD'),
	'charset' => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'prefix' => ''
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();