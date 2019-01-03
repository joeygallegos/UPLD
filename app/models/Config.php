<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;
class Config extends Eloquent {
	protected $table = 'configuration';
	protected $fillable = ['id', 'item_name', 'item_value', 'item_sequence', 'item_readonly', 'item_description'];
	protected $guarded = ['id'];
	public $timestamps = false;

	// Get a section from the config
	public static function get($section = '', $default = '') {
		$item = Config::where('item_name', '=', $section)->first();
		$value = $item->item_value;
		
		// Return default value
		if (is_null($value) || $value === '') return htmlspecialchars($default);
		return htmlspecialchars($value);
	}

	public static function getRaw($section = '', $default = '') {
		$item = Config::where('item_name', '=', $section)->first();
		$value = $item->item_value;
		
		// Return default value
		if (is_null($value) || $value === '') return $default;
		return $value;
	}

	public static function updateSection($section = null, $content = null) {
		if (is_null($content)) throw new Exception('Provide a field to update', 1);
		if (is_null($section)) throw new Exception('Can\'t update section with nothing', 1);

		return Config::where('item_name', '=', $section)->first()->update([
			'item_value' => $content
		]);
	}

	public static function getArrayableData() {
		$arr = [];
		foreach (Config::all() as $item) {
			$arr[$item->item_name] = $item;
		}
		return $arr;
	}
}