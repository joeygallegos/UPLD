<?php
use Illuminate\Database\Eloquent\Model as Eloquent;
class Post extends Eloquent {
	protected $table = 'posts';
	protected $fillable = ['id', 'user_id', 'hash', 'content', 'title', 'created_at', 'updated_at'];

	protected $guarded = ['id'];
	public $timesetamps = true;

	public function user() {
		return $this->belongsTo('User');
	}
}
