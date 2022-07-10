<?php
namespace DigitalMx\jotr;

/* This file contains tables of names, lists, etc
	used throughout the site and grouped into
	  Definitions (db tables, )
	  Definitions_Email (status codes, )
	  Definitions_News (sections, types,
	  Definitions_Member (status, aliases, security levels)
	Most are public static vars, so call like
	  Definitions::$dbTable

*/
class Definitions {

	// File Definitions
	public static $Files = [
		'passwords' => 'passwords.ini'
	];

	public static $uvwarn = [

		'Low' => 'Enjoy',
		'Moderate' => 'Use some sunscreen',
		'High' => "Caution",
		'Very High' => 'Oh no.  Burny burny!',
		'Extreme' => 'Yer dead',

	];

public static $scale_color = [

		'Low' => 'green',
		'Good' => 'green',
		'Moderate' => 'yellow',
		'Unhealthy for Sensitive Groups' => 'orange',
		'High' => "orange",
		'Unhealthy' => 'red',
		'Very High' => 'red',
		'Very Unhealthy' => 'purple',
		'Extreme' => 'red',
		'Hazardous' => 'maroon',

	];

public static $firewarn = [

		'Low' => 'not bad',
		'Moderate' => 'normal',
		'High' => "a bit high",
		'Very High' => 'no fires',
		'Extreme' => 'carry an extinguisher',

	];

/* apis
JUMBO ROCKWS
	https://api.weather.gov/points/33.9917,-116.1402
 "forecastZone": "https://api.weather.gov/zones/forecast/CAZ560",
"county": "https://api.weather.gov/zones/county/CAC065",
"fireWeatherZone": "https://api.weather.gov/zones/fire/CAZ230",

Cottonwood
https://api.weather.gov/points/33.7485,-115.8211
Indian cove

Black rock
https://api.weather.gov/points/34.0733,-116.3907
29 palms

Keys View
https://api.weather.gov/points/33.9272,-116.1875

hq
https://api.weather.gov/points/
*/

// weather.gov grid points


public static $gridpoints = [
	'hq' => 'VEF/72,12',
	'jr' => 'PSR/13,102',
	'cw'=>	'PSR/23,89',
	'br' => 'PSR/4,107',
	'kv' => 'PSR/11,99',



	];

public static $coordinates = [
	'jr' => '33.9917,-116.1402',
	'br' => '34.0733,-116.3907',
	'kv' => '33.9272,-116.1875',
	'hq' => '34.1348,-116.0815',
	'cw' => '33.7485,-115.8211',
];




	public static function uv_scale ($uv) {
		// sets uv name based on index
		if (!is_numeric($uv)){
			throw new Exception ("non-numeric uv index");
		}
		if ($uv <= 2.9) return "Low";
		if ($uv <= 5.9) return "Moderate";
		if ($uv <= 7.9) return "High";
		if ($uv <= 10.9) return "Very High";
		return "Extreme";


	}

	public static function aq_scale ($uv) {
		// sets uv name based on index
		if (!is_numeric($uv)){
			throw new Exception ("non-numeric uv index");
		}
		if ($uv <= 51) return "Good";
		if ($uv <= 100) return "Moderate";
		if ($uv <= 150) return "Unheadly for Sensitive Groups";
		if ($uv <= 200) return "Unhealthy";
		if ($uv <= 300) return "Very Unhealthy";

		return "Hazardous";


	}


	public static $sitenames = [
		'ic' => 'Indian Cove',
		'jr' => 'Jumbo Rocks',
		'sp' => 'Sheep Pass (group)',
		'hv' => 'Hidden Valley',
		'be' => 'Belle',
		'wt' => 'White Tank',
		'ry' => 'Ryan',
		'br' => 'Black Rock',
		'cw' => 'Cottonwood',
		'hq'	=> 'Twentynine Palms',
		'kv' => 'Keys View',
	];

	public static $campsites = [
		'ic' => 101,
		'jr' => 124,
		'sp' => 6,
		'hv' => 50,
		'be' => 18,
		'wt' => 15,
		'ry' => 31,
		'br' => 99,
		'cw' => 62,
	];

	public static $campfeatures = [
		'ic' => 'G',
		'jr' => '',
		'sp' => 'G',
		'hv' => '',
		'be' => '',
		'wt' => '',
		'ry' => '',
		'br' => 'H,D,W',
		'cw' => 'D,W,',
	];

	public static $cgavail = [
		'Open',
		'Reservation',
		'Closed',
	];


		public static $caches  = array (
				'light' => REPO_PATH . "/data/light.json",
				'weather' => REPO_PATH . "/data/weather.json",
				'fire' => REPO_PATH . "/data/fire.json",
				'air' => REPO_PATH . "/data/air.json",
				'info' => REPO_PATH . "/data/info.json",
				'camps' => REPO_PATH . "/data/camps.json",
				'calendar' => REPO_PATH . "/data/calendar.json",
				'uv' => REPO_PATH . "/data/uv.json",
				'today' => REPO_PATH . "/data/today.json",
			);

// time before refresh in minutes.  0 means
// cache is static except for update by
// the admin screen.  No outside retrieval.
		public static $cache_times  = array (
				'light' => 2400,
				'weather' => 2400,

				'air' => 2400,

				'camps' => 0,
				'uv' => 60 ,
				'calendar' => 0,
				'today' => 0,
			);


	public static function uv_warn($uvd) {
		return self::$uvwarn[$uvd] ?? 'not defined';
	}

	public static function scale_color($uvd){
		return self:: $scale_color[$uvd] ?? '';
	}

	public static function fire_warn($fdesc) {
		return self::$firewarn[$fdesc];
	}

	public static function getEcode($ecode) {
		return self::$ecodes[$ecode];
	}

	public static function getMaxtime($section) {
		return (60 * self::$cache_times[$section] );
	}
}
