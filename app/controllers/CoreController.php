<?php

class CoreController {

	public static function getIndex() {
		return View::make('front.home', [
			'title' => 'Login - UPLD',
			'styles' => [
				'clean',
				'form-reset',
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

	public static function getSettings($app, $user) {
		return View::make('front.settings', [
			'title' => 'Settings - UPLD',
			'styles' => [
				'clean',
				'admin'
			],
			'user' => $user
		]);
	}
}