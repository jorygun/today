<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	//require_once $_SERVER['DOCUMENT_ROOT'] . '/init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;

//END START



// parse_str($_SERVER["QUERY_STRING"], $params);
// //u\echor ($params);
// echo "Syntax: r2.php?Force&Index&Data;\nForce to force updates; Index to xfer result to index.php; Data to show data arrays" . BRNL;
//
// u\echor ($params,'Parameters:') . BRNL;
// $Force = (isset($params['Force']))? 1:0 ; #1 = force updates now. 0 = use regular timing.
// if ($Force) echo "Forcing updates" .BRNL;
// $only = $params['only'] ?? ''; // only=section






// u\echor (CACHE); exit;

/* SECTIONS
data is divided into sections. Each section has its own update
process and cached data file.

info	Contains latest update time, current date being displayed

weather	contains forecasts for today and next 2 days (3 days) for each designated location: geneally jumbo, 29, cottonwood and black rock.

air	air quality info for jumbo rocks

fire	fire danger and messages (derived from local and defs)

camps	availability and status for each campground.

local	manual weather notice, fire notice, current fire danger, pithy statement, announcements.  General campground announcement


light	sunrise, moon phase, etc

calendar data from park calendar assembled into a json file

*/

// model for building and reading the local array



class Today {

public static $dummy_today = array
(
		'fire_level' => 'Low',

		'camps' => array
			(
				'cgavail' =>  array(
					'ic' => 'Reservations',
					'jr' => 'Reservations',
					'sp' => 'Reservations',
					'hv' => 'Open',
					'be' => 'Closed',
					'wt' => 'Closed',
					'ry' => 'Reservations',
					'br' => 'Reservations',
					'cw' => 'Reservations',
					),
				'cgstatus' => array (
					'ic' => 'Partially Open',
					'jr' => '',
					'sp' => '',
					'hv' => '',
					'be' => 'May open if Hidden Valley Fills Up',
					'wt' => '',
					'ry' => '',
					'br' => '',
					'cw' => '',
					),
				),

			'target' => 'July 3, 2022',
			'updated' => '2 Jul 21:55',
			'fire_level' => 'High',
			'pithy' => 'Something Pithy',
			'announcements' => '40 Palms Canyon is closed for the summer.',
			'weather_warn' => 'Heat Warning!',

);


public static $dummy_calendar = array
			 (
            0 => array
                (
                    'event_datetime' => 'April 1 8:00pm',
                    'event_location' => 'Indian Cove amphitheater',
                    'event_type' => 'Ranger Program',
                    'event_title' => 'Adaptations to the the Desert',
                    'event_note' => '',
                ),

            '1' => array
                (
                    'event_datetime' => 'Tomorrow 10 am',
                    'event_location' => 'Discovery Trail trailhead',
                    'event_type' => 'Walk and Talk',
                    'event_title' => 'Where these rocks came from',
                    'event_note' => 'Gather at the crosswalk on Park Drive',
                ),

            '2' => array
                (
                    'event_datetime' => 'mar 15 18:00',
                    'event_location' => 'Joshua Tree Cultural Center',
                    'event_type' => 'Ranger Talk',
                    'event_title' => 'tbd',
                    'event_note' => '',
                ),
            );



###############################

public function __construct($c){
	$this->Plates = $c['Plates'];
	$this -> Defs = $c['Defs'];

	$this-> all_sections = ['info','weather','air','camps','fire','calendar'];
	// locations to use for weather report
	$this -> wlocs = ['jr','hq','cw','br'] ;
	$this -> max_age = Defs::$cache_times;
}


public function prepare_today() {
 // get sections needed
	foreach (['air','weather','uv','calendar'] as $section) {
		$y[$section] = $this -> load_cache ($section);
	}
	$y['today'] = $this -> load_today();
//u\echor($y, 'y array for today');
	return $y;
 }

public function prepare_admin() {
// get sections needed for the admin form

	$y = $this -> load_today();
// 	u\echor($y, 'load today()');

	$fire_levels = array_keys(Defs::$firewarn);
	$y['fire_options'] = u\buildOptions($fire_levels,$y['fire_level']);


// camps

	foreach (array_keys(Defs::$campsites) as $cgcode){
		$opt = u\buildOptions(Defs::$cgavail, $y['camps']['cgavail'][$cgcode]);
			$opts[$cgcode]  = $opt;
	}

	$y['camps']['cg_options'] = $opts;

	$y['calendar'] = $this->load_cache('calendar');


// 	u\echor($y, 'Y to admin');
	return $y;
}


public function post_admin ($post) {
 /* insert posted data and dependencies into cacjes
 // l

run this by sending the post from today form or by
sending a saved copy of the last post.

same routine can be used to update indiviual cahces



*/
// u\echor ($post, 'Posted');

//  today cache
	$y=[];
	$y['announcements'] = $post['announcements'];
	$y['updated'] = date('d M H:i');
	$y['pithy'] = $post['pithy'];
	$y['weather_warn'] = $post['weather_warn'];

	$y['fire_level'] = $post['fire_level'];

	$y['camps'] = $post['cg']; // array

	$this -> write_cache('today',$y);


	$y = $post['calendar'];

	foreach ($y as $cal){
		if (!empty($dt = $cal['event_datetime'])){
			// convert text date to time stamp and save a key
			$dts = $this->str_to_ts($dt);
			$cal['dts'] = $dts;
			$x[$dts] = $cal;
		//	u\echor($x,'',STOP);
		}
	}

	ksort ($x, SORT_NUMERIC);
	//u\echor($x,'',STOP);



	$this->write_cache('calendar',$x);

//	u\echor ($z,'Calendar to cache');


}

#-----------   SUBS ------------------------------



private function load_cache ($section) {
		$refresh = 0;
		if (! file_exists (CACHE[$section])) {
			$refresh = 1;
		} else {
			$mtime = filemtime (CACHE[$section]);
// 			echo "$section: <br>";
// 			echo "mtime $mtime; time ";
// 			echo "diff " . time() - $mtime . BRNL;
// 			echo "lim " . $this->Defs->getMaxtime ($section) . BRNL;
			if ($mtime && (time() - $mtime > $this->Defs->getMaxtime ($section) )){
					$refresh = 1;
			}
		}

		if ($refresh) {
			$this->refresh_cache($section);
		}

		$y = json_decode (file_get_contents(CACHE[$section]), true);
		if (empty($y)){
			switch ($section) {
				case 'calendar' :
					$y = self::$dummy_calendar; break;
				default:
			}
		}
		return $y;
}





public function refresh_cache (string $section ) {
	/* loads data from all source fgiles and rebuilds the cache fie compoetely.
	init is array of data to be set as inital conditions.
	= either default init or latest today array.
	*/


	// creates or updates the section's cache file
	//$v = array ('updated' => time());

	// external $w
			switch ($section) {
				case 'weather': $w = $this -> external_weather($this->wlocs) ;break;
				case 'air': $w = $this -> external_airqual_2() ?? []; break;
				case 'light': $w = $this -> external_light() ?? []; break;
				case 'uv': $w = $this -> internal_uv () ; break;

				//case 'calendar': $w=self::$dummy_calendar; break;

				default: $w = [];
		}

		//$z = array_merge($v,$w);

		$this->update_section ($section,$w);

	return true;
}


public function update_section(string $section,array $u) {
		// reads section cache
		// merges in $u
		// saves to cache


		if (file_exists(CACHE[$section])){
			$y = json_decode(file_get_contents(CACHE[$section]), true);
		} else {
			$y = [];
		}

		$z = array_merge($y, $u);
//  		u\echor ($z,"Merged in Update $section", );
		$this->write_cache($section, $z);
		return true;
	}



########   LOAD ###############
#-----------------  LOAD today --------------------

private function load_today() {
		$refresh = false;
		$section = 'today';
		if (! file_exists (CACHE[$section])) {

			$refresh = true;
		}

		if ($refresh) {
			$this->refresh_cache($section);
		}

		$y = json_decode (file_get_contents(CACHE[$section]), true);
		if (empty($y['camps'])){ #test fpr local stuff there
// need to send an alert iuf this happens

			$y = self::$dummy_today;
		}

		$target_date = date('l, d M Y');
		$y['target'] = $target_date;
		$y['updated'] = date ('d M H:i');

		return $y;




}

#-----------------  LOAD EXTERNASL --------------------



private function external_airqual (){
	$section = 'air';
	global $Defs;
	$y = [];

	// external data
// uses api to get aq at jumbo rocks (pinto wye).

	$curl = curl_init();
	$curl_options = curl_options();
	curl_setopt_array($curl,$curl_options);

	curl_setopt($curl, CURLOPT_HTTPHEADER, [
		"X-RapidAPI-Host: air-quality.p.rapidapi.com",
		"X-RapidAPI-Key: 3265344ed7msha201cc19c90311ap10b167jsn4cb2a9e0710e"
		]);
	curl_setopt($curl,CURLOPT_URL, "https://air-quality.p.rapidapi.com/current/airquality?lon=-116.140&lat=33.9917");


	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	$aresp = json_decode($response, true);
	//	u\echor($aresp);
	$msg = $aresp['message'];
	//echo $msg . BRNL;

	if ($msg || $err ) { #quota exceeded?
		$y['aqi'] = 'n/a';
		$y['pm10'] = 'n/a';
		$y['o3'] = 'n/a';
	} else {

		$y['aqi'] = $aresp['data'][0]['aqi'];
		$y['pm10'] = $aresp['data'][0]['pm10'];
		$y['o3'] = $aresp['data'][0]['o3'];
	}


// u\echor($y); exit;
	 // save the array for local caching
	 return $y;
}

private function external_airqual_2 (){
// uses api to get aq at jumbo rocks (pinto wye).


[$lat,$lon] = $this -> split_coord('jr');
$url = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$lat}&lon={$lon}&appid=" . OPENWEATHERMAP_KEY;
// echo $url; //exit;


//echo "Updating air quality" . BRNL;
	$curl = curl_init();
	$curl_options = $this -> curl_options();
	curl_setopt_array($curl,$curl_options);

	curl_setopt($curl,CURLOPT_URL, $url);

	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);

	$aresp = json_decode($response, true);
// u\echor($aresp); //exit;
	$msg = $aresp['message'] ?? '';
	//echo $msg . BRNL;

	if ($msg || $err ) { #quota exceeded?
		$y['aqi'] = 'n/a';
		$y['pm10'] = 'n/a';
		$y['o3'] = 'n/a';
	} else {
		$aqi = $aresp['list']['0']['main']['aqi'];
		$aqi_scale = $this -> Defs->aq_scale($aqi);
		$aqi_color = $this -> Defs->scale_color($aqi_scale);

		$y['aqi'] = $aqi;
		$y['pm10'] = $aresp['list'][0]['components']['pm10'];
		$y['o3'] = $aresp['list']['0']['components']['o3'];
		$y['aqi_scale'] = $aqi_scale;
		$y['aqi_color'] = $aqi_color;
	}

	//$z['air'] = $y;

	return $y;

 }


private function external_weather ($locs) {


// get forecast data for each location
	foreach ($locs as $loc) {
	$ch = curl_init();
	curl_setopt_array($ch,$this -> curl_options() );
		$url = 'http://api.weatherapi.com/v1/forecast.json?key=' . WEATHERAPI_KEY . '&q='. Defs::$coordinates[$loc] . '&days=3&aqi=yes&alerts=yes';

		curl_setopt($ch, CURLOPT_URL,$url);
		$resp = curl_exec($ch);
		$err = curl_error($ch);
		if ($err) {
			echo "cURL Error #:" . $err;
			exit;
		}
		curl_close ($ch);
	// convert ot php arrays
		$aresp = json_decode($resp, true);
//    u\echor($aresp);# exit;


		for ($i=0;$i<3;++$i){
			$x = $aresp['forecast']['forecastday'][$i]; #array
//if ($i==0) u\echor ($x);
			$period = $i;
		//	echo "period: $period";



			$fdate = \DateTime::createFromFormat('Y-m-d', $x['date']);

			$y[$loc][$period] = array(
				'epoch' => $x['date_epoch'],
				'date' => $fdate->format('l, M j'),
				'High' => round($x['day']['maxtemp_f']) ?? 'n/a',
				'Low' => round($x['day']['mintemp_f']) ?? 'n/a' ,
			//	'winddir' => $x['day']['winddir'],
				'avghumidity' => $x['day']['avghumidity'],
				'maxwind' => round($x['day']['maxwind_mph']),
				'skies' => $x['day']['condition']['text'],
				'rain' => $x['day']['daily_chance_of_rain'],
				// saved to get uv and light
				'uv' => $x['day']['uv'],

				'sunrise' => $this -> time_format( $x['astro']['sunrise']),
				'sunset' => $this -> time_format($x['astro']['sunset']),
				'moonrise' => $this -> time_format($x['astro']['moonrise']),
				'moonset' => $this -> time_format($x['astro']['moonset']),
				'moonphase' => $x['astro']['moon_phase'],
				);
		}
	} #end foreach

	return $y;
}


private function internal_uv() {
	// $curl = start_curl ("https://api.openuv.io/api/v1/uv?lat=34.12&lng=-116.14&dt=2018-01-24T10:50:52.283Z' -H 'x-access-token: f9792ff531cddbab6b41fb6302e7c256'"
// 	$response = curl_exec($curl);
// 	$err = curl_error($curl);
// 	curl_close($curl);
//
// 	if ($err) {
// 		echo "cURL Error #:" . $err;
// 		return;
// 	}

// retrieve uv from the weather report

	$w = $this->load_cache('weather');

	$uv = $w['jr']['0']['uv'];
	//'UV' => $z['jr']['0']['UV'],

	$uv_data = $this -> uv_data($uv);

		$y = array
		(
			'uv' => $uv_data[0],
   		'uvscale'  => $uv_data[1],
   		'uvwarn'  => $uv_data[2],
   		'uvcolor' => $this ->Defs->scale_color($uv_data[1]),
		);

// 		u\echor ($y);
	return $y;
}




// -----------  INIT -------------




// -----------   UTILITY FUNCTIONS -------------

private function time_format($time) {
	// remove leading 0

	if (substr($time,0,1) == '0'){
		$time = substr ($time, 1);
	}

	return $time;
}

private function curl_options () {

	$agent = 'Mozilla/5.0 (NPS.gov/jotr app)';


	$options = [
	CURLOPT_USERAGENT => $agent,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",


	];

	return $options;
}




private function uv_data($uv) {
	// takes numeric uv, returns array of uv, name, warning

			$uvscale = $this -> Defs->uv_scale($uv);
			$uvwarn = $this ->Defs->uv_warn($uvscale);
			return ([$uv,$uvscale,$uvwarn]);
}

public function start_page ($title = 'Today in the Park',$pcode='') {
	$param = '';
	if ($pcode=='s') $param='onLoad="pageScroll()"';

	$text = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <link rel='stylesheet' href = '/today.css' >
	<title>$title</title>
</head>
<body $param>

EOF;
	return $text;
}

private function split_coord ($loc) {
	$coord = Defs::$coordinates[$loc];
	[$lat,$long] = explode(',',$coord);
	return [$lat,$long];
}

private function over_cache_time($section) {
	//global $Defs;
	$filetime = filemtime(CACHE[$section] );
	$limit = Defs::$cache_times[$section] * 60;
	if ($time() - $filetime > $limit) return true;
	return false;
}

private function str_to_ts($edt) {
			try {
				if (empty($edt)) return '';;
				if (! $t = strtotime($edt) )
					throw new RuntimeException ("Illegal date/time: $edt");
				return $t;
			} catch (RuntimeException $e) {
				u\echoalert ($e->getMessage());
				echo "<script>history.back()</script>";
				exit;
			}
		}

private function write_cache(string $section,array $z) {
	file_put_contents(CACHE[$section],json_encode($z));
}




} #end class
