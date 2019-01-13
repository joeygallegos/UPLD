<?php
namespace App\Models;
use App\Models\User;
class Sessions {

	public static function getUser() {
		if (isset($_SESSION['user']) && !(is_null($_SESSION['user']))) {
			return User::where('id', $_SESSION['user']->id)->first();
		}
		return null;
	}
}