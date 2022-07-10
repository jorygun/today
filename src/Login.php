<?php

namespace DigitalMx\jotr;

/*
    get password

    check_pwlevel (min)
    	compares current pw level with min, returns true if >=,
    	otherwise opens screen to get pw and sets level in session.
    get_pwlevel()
    	returns pw level from session
    get_pw()
    	opens login window.  Window gets pw.  Submit calls
    		set_pwlevel
    set_pwlevel(pw)
    	gets level from pw.ini; sets session; returns level
*/

use DigitalMx as u;
use DigitalMx\jotr\Definitions as Defs;


class Login
{



	public function check_pwlevel (int $min_pwl = 0) {
		$pwl  = $this->get_pwlevel();
		if ($pwl >= $min_pwl) return true;

		$pwl = $this->get_pw();

	}

	public function get_pwlevel() {
		return $_SESSION['pw_level'] ?? 0 ;
	}

	private function get_pw() {
		echo <<< eof
		<script>window.open('/login.php','login')</script>
eof;

	}

	public function set_pwlevel ($pw='') {
		$pwlevels = parse_ini_file(REPO_PATH . '/config/'. Defs::$Files['passwords']);
		#u\echor ($pwlevels);
		$pwx = $pwlevels[$pw] ?? 0;
		$pwx = intval($pwx);
		$pwl = 0;
		if (!empty($pwx) && is_integer($pwx) && $pwx > 0) {
			$pwl = $pwx;
		}
		$_SESSION['pw_level'] = $pwl;
		return $pwl;
	}


}
