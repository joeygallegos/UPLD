<?php

function isGet() { 
	return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function isPost() { 
	return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function startsWith($haystack, $needle) {
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

function isAjaxRequest() {
	return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function jsonify($data = null, $pretty = false) {
	if ($data != null) {
		echo $pretty ? json_encode($data, JSON_PRETTY_PRINT) : json_encode($data);
	}
}

function setHeader() {
    header('Content-Type: application/json');
}