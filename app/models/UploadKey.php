<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
class UploadKey extends Eloquent {
	protected $table = 'upload_keys';
	protected $fillable = ['id', 'upld_id', 'user_id', 'code', 'address', 'used', 'created_at', 'used_at'];
	protected $guarded = ['id'];
	public $timestamps = false;
}
