<?php
use Illuminate\Database\Eloquent\Model as Eloquent;
class Contact extends Eloquent {
	protected $table = 'contacts';
	protected $fillable = ['id', 'first_name', 'middle_name', 'last_name', 'gender', 'title', 'other_details'];

	protected $guarded = ['id'];
	public $timesetamps = true;

	public function account() {
		return $this->belongsTo('Customer');
	}
}
