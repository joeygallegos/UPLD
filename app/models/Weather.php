<?php
class Weather {
	public $key = '';
	public $link = 'http://api.openweathermap.org/data/2.5/weather?lat=29.8017696&lon=-95.74140229999999&mode=json&type=accurate&units=imperial&appid=';

	function __construct($key) {
		$this->key = $key;
	}

	/**
	 * Returns clean array of weather data
	 * @return Array - Cleaned data
	 */
	public function grab() {
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $this->link . $this->key);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($c);
        curl_close($c);
        return json_decode($result, true);
	}

}