<?php
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountCredential extends Eloquent {
	use SoftDeletes;

	protected $guarded = ['id'];
	protected $table = 'accounts';
	public $timestamps = true;

	protected $hidden = [
		'enc_password',
		'enc_key',
		'enc_iv'
	];

	// protected $casts = [
	// 	'deleted' => 'boolean',
	// ];

	protected $fillable = [
		'id',
		'user_id',
		'hash',

		'nickname',
		'loginid',

		// enc data
		'enc_password',
		'enc_key',
		'enc_iv',

		'loginlink',

		// updated_at for new passwords
		'password_changed_at',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	public function toHydrationArray() {
		$data = [];

		// loop attributes
		foreach ($this->fillable as $attribute) {
			if (in_array($attribute, $this->hidden)) continue;
			$data[$attribute] = $this->$attribute;
		}

		// decrypted password
		$data['dec_password'] = $this->getDecryptedPassword();

		// formatted date
		$data['updated_at'] = $this->updated_at->format('m/d/Y');

		// status array
		$data['status_data'] = $this->getPasswordStatus();

		return $data;
	}

	/**
	 * Get the status data as an array
	 * @return Array get the password status
	 */
	public function getPasswordStatus() {

		$ranges = [
			15 => 'NEW',
			30 => 'RECENT',
			60 => 'MODERATE',
			90 => 'RISKY',
			120 => 'DANGEROUS'
		];

		$data = [];
		$daysAgo = 121;//$this->password_changed_at->diffInDays(Carbon::now());
		$lastRange = 0;
		foreach ($ranges as $range => $status) {
			if (($lastRange <= $daysAgo) && ($daysAgo <= $range)) {
				$data = [
					'range' => $range,
					'status' => $ranges[$range]
				];
			}
			$lastRange = $range;
		}
		$data['days_ago'] = $daysAgo;
		return $data;
	}
	
	public function getDecryptedPassword() {
		return openDecrypt($this->enc_password, $this->enc_key, $this->enc_iv);
	}
}