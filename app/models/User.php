<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;
class User extends Eloquent {
	protected $guarded = ['id'];
	protected $table = 'users';
	
	protected $hidden = [
		'password',
		'salt'
	];

	protected $fillable = [
		'username',
		'password',
		'salt',
		'admin',
		'locked'
	];
	
	public $timestamps = true;

	public function alias() {
		return $this->hasOne('App\Models\Alias');
	}
	
	public function authentication() {
		return $this->hasOne('App\Models\Authentication');
	}

	public function posts() {
		return $this->hasMany('App\Models\Post');
	}
	
	/**
	 * return if the user is admin
	 *
	 * @return admin = true
	 */
	public function isAdmin() {
		return $this->admin == 1;
	}
	
	/**
	 * return if the user is locked
	 *
	 * @return locked = true
	 */
	public function isLocked() {
		return $this->locked == 1;
	}
}
