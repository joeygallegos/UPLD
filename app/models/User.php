<?php
use Illuminate\Database\Eloquent\Model as Eloquent;
class User extends Eloquent {

	protected $table = 'users';
	protected $hidden = ['password', 'salt'];

	protected $fillable = ['username', 'password', 'salt', 'admin', 'locked'];
	protected $guarded = ['id'];
	public $timestamps = true;

	public function alias() {
		return $this->hasOne('Alias');
	}

	public function posts() {
		return $this->hasMany('Post');
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
