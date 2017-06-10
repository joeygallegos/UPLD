<?php

class CoreController {

	public static function index() {
		return View::make('front.home', [
			'title' => 'Home',
			'styles' => [
				'clean',
				'style'
			],
			'tags' => [
				'login',
				'home'
			]
		]);
	}

	public static function getDashboard($app, $user) {
		return View::make('front.dashboard', [
			'title' => 'Dashboard - UPLD',
			'styles' => [
				'clean',
				'admin'
			],
			'user' => $user
		]);
	}
}