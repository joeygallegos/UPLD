<?php
/**
 * This class is mostly used for grabbing global constants for the site's
 * important features
 */
use Illuminate\Database\Eloquent\Model as Eloquent;
class Config extends Eloquent {
	protected $table = 'configuration';
	protected $fillable = ['id', 'key_name', 'key_value'];
	protected $guarded = ['id'];
	public $timestamps = false;

	// get a section from the config
	public static function get($section = '', $default = '') {
		$content = Config::where('key_name', '=', $section)->first()->content;

		// Return default content
		if (is_null($content)) return htmlspecialchars($default);
		return htmlspecialchars($content);
	}

	public static function updateSection($section = null, $content = null) {

		if (is_null($content)) throw new Exception('Provide a field to update', 1);
		if (is_null($section)) throw new Exception('Can\'t update section with nothing', 1);

		Config::where('key_name', '=', $section)->first()->update(array(
				'key_value' => $content
			));
	}
}
