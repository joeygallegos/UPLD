<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
class Upld extends Eloquent {
	protected $table = 'upld';
	protected $fillable = ['id', 'user_id', 'hash', 'extension', 'created_at', 'updated_at'];
	protected $guarded = ['id'];

	public $timesetamps = true;

	public function user() {
		return $this->hasOne('User');
	}

}
