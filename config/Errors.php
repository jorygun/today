<?php
namespace DigitalMx\jotr;

class Errors {

	static $ecodes = array (
		0 => 'Undefined Error',
		1 => 'Date format not recognzied',
	);



	public function getEcode($code) {
		return self::$ecodes[$code];
	}

}
