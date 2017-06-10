<?php
/**
* Person class for storing data about a person
*/
use Illuminate\Database\Eloquent\Model as Eloquent;
class Person extends Eloquent {
	protected $table = 'persons';
	protected $fillable = ['first_name', 'last_name', 'phone_number', 'address', 'notes'];
	protected $guarded = ['id'];
	public $timestamps = false;
}