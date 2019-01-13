<?php

if (!function_exists('sanitize')) {
	function sanitize($string) {
		return htmlspecialchars(strip_tags($string));
	}
}

if (!function_exists('isNullOrEmptyString')) {
	function isNullOrEmptyString($string) {
		return (!isset($string) || trim($string) === '');
	}
}

if (!function_exists('getBaseDirectory')) {
	function getBaseDirectory() {
		return dirname(__DIR__);
	}
}

if (!function_exists('directoryExists')) {
	function directoryExists($dir) {
		return file_exists($dir);
	}
}
if (!function_exists('createDirectory')) {
	function createDirectory($dir) {
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
			return true;
		}
		return false;
	}
}

if (!function_exists('createFile')) {
	function createFile($dir, $filename, $ext, $data) {
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		$file = fopen($dir . $filename . '.' . $ext, 'w');
		fwrite($file, $data);
		fclose($file);
	}
}

if (!function_exists('getRequestAddress')) {
	function getRequestAddress() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER["REMOTE_ADDR"];
		}
	}
}

// The IV field will store the initialisation vector used for encryption. The storage requirements depend on the cipher and mode used. The password field will be hashed using a one-way password hash.
if (!function_exists('openEncrypt')) {
	function openEncrypt($string) {
		$method = 'AES-256-CBC';
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
		$key = substr(hash('sha256', $iv), 0, 16);

		return [
			'string' => openssl_encrypt($string, $method, $key, $options = 0, $iv),
			'method' => $method,
			'key' => $key,
			'iv' => $iv
		];
	}
}

if (!function_exists('openDecrypt')) {
	function openDecrypt($string, $key, $iv) {
		$method = 'AES-256-CBC';
		return openssl_decrypt($string, $method, $key, $options = 0, $iv);
	}
}