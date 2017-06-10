<?php
use Illuminate\Database\Eloquent\Model as Eloquent;
class Customer extends Eloquent {
	protected $table = 'customers';
	protected $fillable = ['customer_id', 'customer_name', 'main_contact_id'];

	protected $guarded = ['customer_id'];

	public $timestamps = true;
}
