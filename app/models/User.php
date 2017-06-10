<?php
use Illuminate\Database\Eloquent\Model as Eloquent;
class User extends Eloquent {

	protected $table = 'users';
	protected $hidden = ['password', 'salt'];

	protected $fillable = ['username', 'password', 'salt', 'admin'];
	protected $guarded = ['id'];
	public $timesetamps = true;

	public function alias() {
		return $this->hasOne('Alias');
	}

	public function posts() {
		return $this->hasMany('Post');
	}
}
