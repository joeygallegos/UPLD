<?php
use Illuminate\Database\Eloquent\Model as Eloquent;

class Alias extends Eloquent {

	protected $table = 'user_aliases';
	protected $fillable = array('id', 'user_id', 'alias_name', 'created_at', 'updated_at');
	public  $timestamps = true;

	public function user() {
		return $this->belongsTo('User');
	}
}
