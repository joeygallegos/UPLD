<?php
class Sessions {
	public static function getUser() {
		if (isset($_SESSION['user']) && !(is_null($_SESSION['user']))) {
			return User::where($_SESSION['user'])->first();
		}
		return null;
	}

	public static function isSafe($toCheck = 0) {
		// Checks
		if (!is_numeric($toCheck)) return false;
		if ($toCheck <= 0) return false;

		if (!is_null(self::getUser())) {
			return self::getUser()->id == $toCheck;
		}
		return false;
	}
}