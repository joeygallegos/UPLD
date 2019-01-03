<?php
namespace App\Validation;
use Respect\Validation\Exceptions\NestedValidationException;
class Validator {
	protected $errors = [];

	public function __construct($container) {
		$this->container = $container;
	}

	public function validate($request, array $rules) {
		foreach ($rules as $field => $config) {
			try {
				$config['validator']->setName($config['clean_name'])->assert($request->getParam($field));
			} catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
			}
		}

		$_SESSION['errors'] = $this->errors;
		return $this;
	}

	public function failed() {
		return !empty($this->errors);
	}

	public function errors() {
		return $this->errors;
	}
}