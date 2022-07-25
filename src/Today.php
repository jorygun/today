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


			'target' => 'July 3, 2022',
			'updated' => '2 Jul 21:55',
			'fire_level' => 'High',
			'pithy' => 'Something Pithy',
			'announcements' => '40 Palms Canyon is closed for the summer.',
			'weather_warn' => 'Heat Warning!',

);

private static $scroll_script = <<<EOT
<script>

function pageScroll() {
    	window.scrollBy(0,3); // horizontal and vertical scroll increments
    	scrolldelay = setTimeout('pageScroll()',50); // scrolls every 100 milliseconds
            if ((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) {
        		scrolldelay = setTimeout('PageUp()',2000);
    		}

}

function PageUp() {
	window.scrollTo(0, 0);
}

</script>


<script>
	let timeout = setTimeout(() => {
  document.querySelector('#target').scrollIntoView();
}, 5000);

(function() {
  document.querySelector('#bottom').scrollIntoView();
})();
</script>


EOT;

private static $snap_script = <<<EOT
<script>

function pageScroll() {
    	window.scrollBy(0,3); // horizontal and vertical scroll increments
    	scrolldelay = setTimeout('pageScroll()',50); // scrolls every 100 milliseconds
            if ((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) {
        		scrolldelay = setTimeout('PageUp()',2000);
    		}

}

function PageUp() {
	window.scrollTo(0, 0);
}

</script>


<script>
	let timeout = setTimeout(() => {
  document.querySelector('#target').scrollIntoView();
}, 5000);

(function() {
  document.querySelector('#bottom').scrollIntoView();
})();
</script>


EOT;


public static $dummy_calendar = array
			 (
            0 => array
                (
                		'dt' => 1639620000,

                    'location' => 'Indian Cove amphitheater',
                    'type' => 'Ranger Program',
                    'title' => 'Adaptations to the the Desert',
                    'duration' => '30 min',
                    'note' => '',
                ),

            '1' => array
                (
                    'dt' => 1657558800,
                    'location' => 'Discovery Trail trailhead',
                    'type' => 'Walk and Talk',
                    'title' => 'Where these rocks came from',
                    'duration' => '30 min',
                    'note' => 'Gather at the crosswalk on Park Drive',
                ),

            '2' => array
                (
                    'dt' => 163962000,
                    'location' => 'Joshua Tree Cultural Center',
                    'type' => 'Ranger Talk',
                    'title' => 'tbd',
                    'duration' => '30 min',
                    'note' => '',
                ),
            );



###############################

public function __construct($c){
	$this->Plates = $c['Plates'];
	$this -> Defs = $c['Defs'];

	// locations to use for weather report
	$this -> wlocs = ['jr','hq','cw','br'] ;
	$this -> airlocs = ['jr','cw','br'];

	$this -> max_age = Defs::$cache_times;
	$this -> properties = $this->load_cache('properties');

}

public function rebuild($force = false) {
	// rebuilds caches and regenerates today pages

	$y = $this->prepare_today ($force);
	// set forecee to true to force all cahces to rebuild now, instead of on schedule

	$page_body = $this->Plates -> render('today',$y);

	$static_page = $this->start_page('Today in the Park (static)')
		. $page_body;
	file_put_contents (SITE_PATH . '/pages/today.php',$static_page);

	$scroll_page = $this->start_page('Today in the Park (scrolling)','s')
		. $page_body . self::$scroll_script;
	file_put_contents( SITE_PATH . '/pages/scroll.php', $scroll_page);

	$snap_page = $this->start_page('Today in the Park (snap)','p')
		. $page_body  . self::$snap_script;
	file_put_contents( SITE_PATH . '/pages/snap.php', $snap_page);


	$page_body_new = $this->Plates -> render ('today2',$y);
	$new_page = $this->start_page('Today in the Park (weather.gov)')
		. $page_body_new;
	file_put_contents (SITE_PATH . '/pages/today2.php',$new_page);

	$page_body_con = $this->Plates -> render ('today3',$y);
	$new_page = $this->start_page('Today in the Park (condensed)')
		. $page_body_con;
	file_put_contents (SITE_PATH . '/pages/today3.php',$new_page);

	$page_body_txt = $this->Plates -> render ('today4',$y);
	$new_page = $this->start_page('Today in the Park (text only)')
		. $page_body_txt ;
	file_put_contents (SITE_PATH . '/pages/today4.php',$new_page);

	$page_body_em = $this->Plates -> render ('today5',$y);
	$new_page = $this->start_page('Today in the Park (for email)')
		. $page_body_em ;
	file_put_contents (SITE_PATH . '/pages/today5.php',$new_page);

	echo "Pages updated" . BRNL;
}

public function prepare_today($force=false) {
 /*set force true or false to force cache updates
  get sections needed and assemble inito data array y
  which is ready for the template to use.

  all the raw data is read into the y array from caches.
  Then the information is transferred into the z array, which is what will actually be used to generate the pages.
  The transformation involves choosing which parameters will be used
  and compiling some computed things like colors for levels and
  text for levels.



  */


	foreach (['wgov','wapi','airowm','airnow','calendar','admin','wgova'] as $section) {
		$y[$section] = $this -> load_cache ($section, $force);
	}
// 	u\echor ($y, 'y array into today');

//	echo 'Version: ' . $v . BRNL; exit;
$z=[];
	$z['version'] = file_get_contents(REPO_PATH . "/data/version") ;
	$z['target'] = date('l M j, Y');

	$z['admin'] = $y['admin'];
		//clean text for display (spec chars, nl2br) but don't change stored info.
	foreach(['pithy','fire_warn','weather_warn','announcements'] as $txt){
		if (!empty ($y['admin'][$txt])) {
			$z['admin'][$txt] = $this->clean_text($y['admin'][$txt]);
		}
	}

	$z['wapi']['fc'] = $this->format_wapi($y['wapi']);
	$z['wgov']['fc'] = $this->format_wgov($y['wgov']);

	$z['light']= $z['wapi']['fc']['light'];
	$z['light']['moonpic'] = $this->Defs->getMoonPic($z['light']['moonphase']);

	$z['uv'] = $this->uv_data($z['wapi']['fc']['forecast']['jr'][0]['uv']);

	$z['fire'] = $this->fire_data($y['admin']['fire_level']);

	$z['air'] = $this->format_airowm($y['airowm']);

	$z['camps']['cgavail'] = $y['admin']['cgavail'];
	$z['camps']['cgstatus'] = $y['admin']['cgstatus'];

	$z['calendar'] = $y['calendar'];





 //  u\echor($z, 'z array for today', STOP);
	return $z;
}


public function prepare_admin() {
// get sections needed for the admin form
	$y['admin'] = $this->load_cache('admin');
// 	u\echor ($y, 'read admin cache', NOSTOP);

$fire_levels = array_keys(Defs::$firewarn);
	$y['admin']['fire_level_options'] = u\buildOptions($fire_levels,$y['admin']['fire_level']);

// camps
	foreach (array_keys(Defs::$campsites) as $cgcode){
		$opt = u\buildOptions(Defs::$cgavail, $y['admin']['cgavail'][$cgcode]);
			$opts[$cgcode]  = $opt;
	}
	$y['admin']['cg_options'] = $opts;

	$y['calendar'] = $this->load_cache('calendar');
	$y['alerts'] = $this->load_cache('alerts');
// 	u\echor ($y, 'Y to admin',NOSTOP);
	return $y;
}


public function post_admin ($post) {
 /* insert posted data and dependencies into cacjes


*/
// u\echor ($post, 'Posted');

//  admin cache
	$y=[];
	$y['announcements'] = $post['announcements'];
	$y['updated'] = date('d M H:i');
	$y['pithy'] = u\despecial($post['pithy']);
//fire
	$y['fire_warn'] = $post['fire_warn'];
	$y['fire_level'] = $post['fire_level'];
//weather
	$y['weather_warn'] = $post['weather_warn'];

	$y['cgavail'] = $post['cgavail']; // array
	$y['cgstatus'] = $post['cgstatus']; // array
// 	u\echor ($y,'to write admin cache',STOP);
	$this -> write_cache('admin',$y);


// calendar
	$y = $post['calendar'];
//   u\echor($y,'incoming calendar',NOSTOP);
	foreach ($y as $n => $cal){
// u\echor($cal);
		if (!empty($cal['cdatetime'])){
			// convert text date to time stamp and save a key

			$dt = $this->str_to_ts($cal['cdatetime']);
			$cal['dt'] = $dt;
			$x[] = $cal;
		//	u\echor($x,'',STOP);
		}
	}

	$xs = $this->element_sort($x,'dt');
// u\echor($x,'c2');
// u\echor($xs,'post sort',STOP);
	$this->write_cache('calendar',$xs);


//rebuild the pages
	$this->rebuild();




}

#-----------   SUBS ------------------------------



public function load_cache ($section,bool $force=false) {
		$refresh = $force;

		if (! $refresh && !file_exists (CACHE[$section])) {
			$refresh = true;
		} elseif (!$refresh) {
			$mtime = filemtime (CACHE[$section]);
			$maxtime = $this->Defs->getMaxtime ($section) ;
			// $maxtime set to 0 if cache is maintanined elswhere,
			// by admin or by resetting another cache.
			$diff = time() - $mtime;
			if ($maxtime && ( $diff > $maxtime )){
					$refresh = true;
					echo "Timeout on $section cache" . BRNL;
			}
		}

//	if ($section == 'admin') $refresh = true;

			//echo "load $section cache: refresh " , ($refresh)?'true':'false' , BRNL;
		if ($refresh) {
			if (! $this->refresh_cache($section) ) {
				echo "Unable to refresh cache: $section.  Using old version." . BRNL;
			}
		}

		$y = json_decode (file_get_contents(CACHE[$section]), true);
		return $y;
}




public function refresh_cache (string $section ) {
	/* loads data from all source fgiles and rebuilds the cache fie compoetely.
	init is array of data to be set as inital conditions.
	= either default init or latest today array.
	*/


	// creates or updates the section's cache file
	//$v = array ('updated' => time());

	echo " Refreshing $section" . BRNL;

	// external $w
			switch ($section) {
				case 'wapi':
					if (! $r = $this->get_external ($section,$this->wlocs) ){
						// failed to get update.  Warn and go on
						echo "Warning: attempt to reload $section failed.";
						return false;
					}

				//	if (!$w = $this -> format_wapi($r) ){
				// 		echo "Warning: failed to parse data returned from $section";
// 						return false;
// 					}
					$this->write_cache ($section,$r);
					break;
				case 'airowm':
					if (! $r = $this->get_external ($section,$this->airlocs) ){
						// failed to get update.  Warn and go on
						echo "Warning: attempt to reload $src failed.";
						return false;
					}

				//	if (!$w = $this -> format_airowm($r) ){
// 						echo "Warning: failed to parse data returned from $section";
// 						return false;
// 					}
					$this->write_cache ($section,$r);


					break;

				case 'calendar' :
					if (!file_exists(CACHE['calendar'])) {
						$w = self::$dummy_calendar;
					}
					$w = $this->filter_calendar();;
					$this -> write_cache($section,$w);
					break;

				case 'properties':
					$locs = ['jr','cw','hq','br','kv'];
					$w = $this->set_properties(plocs);
					if (!$w)die ("no properties");
					break;

				case 'wgova':
					if (! $r = $this->get_external ($section,['hq']) ){
					echo "Warning: attempt to reload $section failed.";
						return false;
					}
					$this->write_cache ($section,$r);
					break;

				case 'wgov':
					if (! $r = $this->get_external ($section,$this->wlocs) ){
						// failed to get update.  Warn and go on
	//					u\echor ($r,'in refresh cache');
						echo "Warning: attempt to reload $section failed.";
						return false;
					}
				//	if (!$w = $this -> format_wgov($r) ){
// 						echo "Warning: failed to parse data returned from $section";
// 						return false;
// 					}
					$this->write_cache ($section,$r);
					break;
				case 'admin':
						$w = self::$dummy_today;
						$this->write_cache($section,$w);
						break;

				case 'alerts':
						$w= $this->get_alerts();
						$this -> write_cache($section,$w);
						break;

				case 'airnow':
					if (! $r = $this->get_external ($section,$this->airlocs) ){
						// failed to get update.  Warn and go on
						echo "Warning: attempt to reload $section failed.";
						return false;
					}
					//if (!$w = $this -> format_airnow($r) ){
// 						echo "Warning: failed to parse data returned from $section";
// 						return false;
// 					}
					$this->write_cache ($section,$r);

					break;

				case 'airq':
					if (! $r = $this->get_external ($section,$this->airlocs) ){
						// failed to get update.  Warn and go on
						echo "Warning: attempt to reload $section failed.";
						return false;
					}
					//if (!$w = $this -> format_airq($r) ){
// 						echo "Warning: failed to parse data returned from $section";
// 						return false;
// 					}
						$this -> write_cache($section,$r);
						break;



				case 'admin':

					return true;
					break;

				default: return false;
		}

	return true;
}

private function filter_calendar() {
	/*
		removes expired events from calendar and sort by date
		calenar = array (
			0 = array (dt,type,title,location,note),
			1 = ...
			);
	*/

	$z=[];

		$y = json_decode (file_get_contents(CACHE['calendar']), true);;

//  	u\echor($y,'cal loaded');

	// ignore invalid dt or dt older than now
	// set first term in if to 1 to prevent filtering
	foreach ($y as $cal){
		if ( 0 || (is_numeric($cal['dt']) && (time() < $cal['dt']) )){
			$z[] = $cal;
		}
	}
//  		u\echor($z,'cal filtered', STOP);
	if (!empty($z)){
		$z = $this->element_sort($z, 'dt');
	}
	return $z;

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

// private function load_today() {
// 		$refresh = false;
// 		$section = 'today';
// 		if (! file_exists (CACHE[$section])) {
//
// 			$refresh = true;
// 		}
//
// 		if ($refresh) {
// 			$this->refresh_cache($section);
// 		}
//
// 		$y = json_decode (file_get_contents(CACHE[$section]), true);
// 		if (empty($y['camps'])){ #test fpr local stuff there
// // need to send an alert iuf this happens
// 			$y = self::$dummy_today;
// 		}
// // 	u\echor($y,'loaded today');
//
//
//
// // u\echor($y,'today after clean', STOP);
//
// 		$target_date = date('l, d M Y');
// 		$y['target'] = $target_date;
// 		$y['updated'] = date ('d M H:i');
//
// 		return $y;
//
//
//
//
// }

#-----------------  LOAD EXTERNASL --------------------

public function get_external (string $src,array $locs=['hq']) {
	/*
		supply one of the source codes ('wapi') and an array
		of at least one valid location (even if not used)

		will return array of key data from source urls for each locatrion
		[
			updated => time
			sourve => code
			data = [
				loc => response array,
				loc => response array,
			]
		]

		Routine retrieves data, retries if failure, and checks for
		one key array value that must be present at some level of the result.
		Set name of required field in "expected" in the location switch.
		If blank, no test will be made.
	*/


	$x=[];
	$src_name = $this->Defs->getSourceName($src);
//echo "src: $src_name" . BRNL;


	foreach ($locs as $loc) {
		[$lat,$lon] = $this -> split_coord($loc);
		$curl_header = [];
		switch ($src) {
			case 'airq' :


				$expected = 'aqi'; #field to test for good result
				$curl_header = [
			"X-RapidAPI-Host: air-quality.p.rapidapi.com",
			"X-RapidAPI-Key: 3265344ed7msha201cc19c90311ap10b167jsn4cb2a9e0710e"
				];

				$url = "https://air-quality.p.rapidapi.com/current/airquality?lon=$lon&lat=$lat";
				$expected = '';

				break;

			case 'airowm':
				$url = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$lat}&lon={$lon}&appid=" . $this->Defs->getKey('openweathermap');
				$expected = ''; #field to test for good result
				break;

			case 'airnow':
				$url = "https://www.airnowapi.org/aq/observation/latLong/current/?format=application/json&latitude=$lat&longitude=$lon&distance=25&API_KEY=" . $this->Defs->getKey('airnow');
				$expected = 'AQI'; #field to test for good result
				break;

			case 'wapi':
				$url = 'http://api.weatherapi.com/v1/forecast.json?key=' . $this->Defs->getKey('weatherapi') . '&q='. $this->Defs->getCoords($loc) . '&days=3&aqi=yes&alerts=yes';
					$expected = '';

					break;
			case 'wgov':
				$url = "https://api.weather.gov/gridpoints/" . $this->Defs->getGridpoints($loc) ."/forecast";
					$expected = 'properties';
				break;

			case 'props':
				$url = "https://api.weather.gov/points/$lat,$lon";
				//(https://api.weather.gov/points/{lat},{lon}).
				$expected = 'properties';
				break;

			case 'wgova':
				$url = 'https://api.weather.gov/alerts/active/zone/CAZ230';
				$expected = '';

				break;

			default: die ("Unknown source requested for external data: $src");

		}


// attempt to get data.  3 retries.
	if (!$aresp = $this->get_curl($src,$url, $expected, $curl_header) ) return false;
	$x[$loc] = $aresp;
	} # next loc

	return $x;
}
/*
'airq' => 'air-quality.p.rapidapi.com/current',
	'owm' => 'api.openweathermap.org',
	'now' => 'airnowapi.org/observation',
	'wapi' => 'api.weatherapi.com forecast',
*/

public function format_airq ( $r){

	$x=[];
	$x['update'] = time();


	foreach ($r as $loc => $d){
		$y['aqi'] = $d['data'][0]['aqi'];
		$y['pm10'] = $d['data'][0]['pm10'];
		$y['o3'] = $d['data'][0]['o3'];

		$x[$loc] = $y;
	}

	return $x;
}





public function format_airowm ($r){
	$x=[];
	$x['update'] = time();

	foreach ($r as $loc => $d){
			$aqi = $d['list']['0']['main']['aqi'];
			$aqi_scale = $this -> Defs->aq_scale($aqi);
			$aqi_color = $this -> Defs->scale_color($aqi_scale);

			$y['aqi'] = $aqi;
			$y['pm10'] = $d['list'][0]['components']['pm10'];
			$y['o3'] = $d['list']['0']['components']['o3'];
			$y['aqi_scale'] = $aqi_scale;
			$y['aqi_color'] = $aqi_color;
			$y['dt'] = $d['list']['0']['dt'];

			$x[$loc] = $y;
	}

	return $x;
}



public function format_airnow ($r){
		$x=[];
	$x['update'] = time();


/* uses airnow.org - referred from eps.gov
	current is good, but forecasts return empty.
	forecast at airnowapi.org/aq/forecast
	now at aq/observation/latlong/current

can get forecast for 29

*/

/*
Array
(
    [0] => Array
        (
            [DateObserved] => 2022-07-16
            [HourObserved] => 8
            [LocalTimeZone] => PST
            [ReportingArea] => Joshua Tree National Park
            [StateCode] => CA
            [Latitude] => 34.0714
            [Longitude] => -116.3906
            [ParameterName] => O3
            [AQI] => 64
            [Category] => Array
                (
                    [Number] => 2
                    [Name] => Moderate
                )

        )

)
*/

	foreach ($r as $loc => $d){

			$aqi = $d['0']['AQI'] ?? '' ;
				$aqi_scale = ($aqi)? $this -> Defs->aq_scale($aqi) : '';
				$aqi_color = ($aqi) ? $this -> Defs->scale_color($aqi_scale) : '';

			$y['aqi'] = $aqi;
			$y['pm10'] = $d['0']['PM10'] ?? 'n/a';
			$y['o3'] = $d['0']['O3']  ?? 'n/a';
			$y['aqi_scale'] = $aqi_scale;
			$y['aqi_color'] = $aqi_color;
			$y['observed_dt'] = strtotime($d[0]['DateObserved'] . ' ' . $d[0]['HourObserved'] . ':00') ;
			$y['reporting'] = $d[0]['ReportingArea'];

			$x[$loc] = $y;
		}



	return $x;

 }




public function format_wapi ($r) {

	$x = [];
	$x['update'] = time();// will end up with $y[$src] = $x;

	foreach ($r as $loc => $ldata){
		 $forecast = $ldata['forecast']['forecastday'];
		 // there are forecasts for 3 days
		for ($i=0;$i<3;++$i){
			$daily = $forecast[$i]; #array

		//	echo "period: $period";

			$fdate = \DateTime::createFromFormat('Y-m-d', $daily['date']);
			$dayts = $fdate->format('s');

			$period = $daily['date'];

			$w[$loc][$i] = array(
				'epoch' => $daily['date_epoch'],
				'date' => $fdate->format('l, M j'),
				'High' => round($daily['day']['maxtemp_f']) ?? 'n/a',
				'Low' => round($daily['day']['mintemp_f']) ?? 'n/a' ,
			//	'winddir' => $daily['day']['winddir'],
				'avghumidity' => $daily['day']['avghumidity'],
				'maxwind' => round($daily['day']['maxwind_mph']),
				'skies' => $daily['day']['condition']['text'],
				'rain' => $daily['day']['daily_chance_of_rain'],
				'visibility' => $daily['day']['avgvis_miles'],
				'uv' => $daily['day']['uv']

				);
		} #end for day

	$x['forecast'] = $w;

	// add airquality current
	 $current_aq = $r[$loc]['current']['air_quality'];
	 $current_aq['updated_ts'] = $r[$loc]['current']['last_updated_epoch'];

	 $x['aq'][$loc] =  $current_aq ;
	} #end location

	// add astro and alerts for jr today

	$astro = $r['jr']['forecast']['forecastday']['0']['astro'];
	$dayuv = $r['jr']['forecast']['forecastday']['0']['day']['uv'];
	$light = array(
				'sunrise' => $this -> time_format( $astro['sunrise']),
				'sunset' => $this -> time_format($astro['sunset']),
				'moonrise' => $this -> time_format($astro['moonrise']),
				'moonset' => $this -> time_format($astro['moonset']),
				'moonillumination' => $astro['moon_illumination'],
				'moonphase' => $astro['moon_phase'],
				'uv' => $dayuv,
		);

	$x['light'] = $light;


	return $x;
}

public function wgov_to_forecast($weathergov) {
	/*
		manipulate data in wgov to create
		a forecast format easier to handle
	*/

		$locs = array_keys($weathergov);
	foreach ($locs as $loc) {
	//echo "loc $loc ";
		if (! $locname = $this->Defs->getLocName($loc) ){continue;}


		$fcarray = $weathergov[$loc]['properties']['periods'];
		$dayno = 0;
		foreach ($fcarray as $period) {
			$day = substr($period['startTime'],0,10);
			$period['dayts'] = strtotime($day);
			$period['locname'] = $locname;
			$fc[$loc][$day][] = $period; // so may be 1 or 2 per day
		}
	}
	return $fc;
}



public function format_wgov ($r) {

	$x=[];
	$x['update'] = time();
	foreach ($r as $loc => $ldata){	//uses weather.gov api directly
		$periods = $ldata['properties']['periods'] ?? '';

		$day = 0;
		$lastday = '';
		foreach ($periods as $p){ // period array]	d
			// two periods per day, for day and night
			// put into one array
// u\echor($p,'period',NOSTOP);
	// set day (key) to datestamp for day, not hours
			$sttime = $p['startTime'];
			$highlow = $p['isDaytime']? 'High':'Low';
			$daytext = date('l, M d',strtotime($sttime));
			if ($daytext != $lastday){
				++$day;
				$lastday = $daytext;
			}
			$p['daytext'] = $daytext;
			$p['highlow'] = $highlow . ' around ' . $p['temperature'] . '&deg; F';

			$x[$loc][$day][] = $p;

		} #end foreach period

	} #end foreach location

	return $x;
}

public function get_alerts () {

	/* must get uniform format for alerts to display right
	headline
	category
	event
	expires
	description
	action





*/
	$y=[];

	$src = 'wapi';

	if ( ! $r = $this->load_cache ($src)) return false;
// 	u\echor($r,'From external',STOP);
		$alerts = $r['jr']['alerts']['alert'];
		$x=[];
		foreach ($alerts as $alertno => $ad){
			$alert_exp = strtotime($ad['expires']);
			if ($alert_exp < time()) continue;
			$alert=[];
			$alert['headline'] = $ad['headline'];
			$alert['category'] = $ad['category'];
			$alert['event'] = $ad['event'];
			$alert['expires'] = $ad['expires'];
			$alert['description'] = $ad['desc'];
			$alert['instruction'] = $ad['instruction'];
			$alert['expire_ts'] = $alert_exp;

			$x[] =$alert;

		}
		$y[$src] = $x;


		$src = 'wgova';
		if ( ! $r = $this->get_external ($src,['jr'] )) return false;
//	u\echor($r,'From external',STOP);
		$items = $r['jr']['features'];

		foreach ($items as $item){
			$ad = $item['properties'];

			$alert_exp = strtotime($ad['expires']);
			if ($alert_exp < time()) continue;
			$alert=[];

			$alert['headline'] = $ad['headline'];
			$alert['category'] = $ad['category'];
			$alert['event'] = $ad['event'];
			$alert['expires'] = $ad['expires'];
			$alert['description'] = $ad['description'];
			$alert['instruction'] = $ad['instruction'];
			$alert['expire_ts'] = $alert_exp;

			$x[] =$alert;

		}
		$y[$src] = $x;


//u\echor($y,'from external  alerts', NOSTOP);

	echo "External alerts updated" . BRNL;
	return $y;
}

public function set_properties ($locs) {
	// gets meta data for each location by lat,lon
	// saves it in data file properties.json
	$src = 'props';
	$x = array('src'=>$src);
	$x['update'] = time();



	if ( ! $r = $this->get_external ($src,$locs)) return false;
	//u\echor($r,'From external',STOP);

	foreach ($r as $loc => $d){	//uses weather.gov api directly
		$y[$loc] = $d['properties'];
	} #end foreach
// u\echor($y,'properties',STOP);

	$this->write_cache('properties',$y);
	echo "Properties updated" . BRNL;
	return true;


}




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

function element_sort(array $array, string $on, $order=SORT_ASC)
{
	/* copied from php manual.
		 sorts a list of arrays by one of the elemnts
		array (
			123 => array (
				'name' => 'asdfl',
				...
			124 => ...

		$sorted = element_sort($unsorted, 'name');


	*/


    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}



private function uv_data($uv) {
	// takes numeric uv, returns array of uv, name, warning
		$uvscale = $this -> Defs->uv_scale($uv);
		$uv = array(
			'uv' => $uv,
			'uvscale' => $uvscale,
			'uvwarn' => $this ->Defs->uv_warn($uvscale),
			'uvcolor' => $this->Defs->get_color($uvscale),
		);
			return ($uv);
}

private function fire_data($fire_level) {
	$fire = array (
		'level' => $fire_level,
		'color' => $this->Defs->get_color($fire_level),
		);

	return $fire;
}
public function start_page ($title = 'Today in the Park',$pcode='') {
	$scbody = '';
	$scstyle = "<link rel='stylesheet' href = '/today.css' >";
	if ($pcode=='s') {$scbody='onLoad="pageScroll()"';
		$scstyle .= "<style>html {scroll-behavior: smooth;}</style>";
	}
	if ($pcode=='p'){
		$scbody = "onLoad='startRotation(10)'";
	}
	if ($pcode=='b'){
		$scstyle = <<<EOT
<!-- Latest compiled and minified CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Latest compiled JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
EOT;
}

	$site_url = SITE_URL;
	$text = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<title>$title</title>
	<script src='/js/snap.js'></script>
	<script src='/js/hide.js'></script>
	$scstyle

</head>
<body $scbody>
<table style='width:100%;'>
<tr style='background-color:black;text-align:right;color:white;'><td style='background-color:black;text-align:right;color:white;'>
Department of the Interior<br>
Joshua Tree National Park
<h1 style="text-align:center;">Today in Joshua Tree National Park</h1>
</td><td style='width:80px;'>
<img src="$site_url/images/Shield-7599-alpha.png" alt="NPS Shield" />
</td></tr>
</table>


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
	$filemtime = filemtime(CACHE[$section] );
	$limit = $this->Defs->getMaxTime($section);
	if ($time() - $filemtime > $limit) return true;
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
	if (empty($z)){
	trigger_error("Writing empty array to $section", E_USER_WARNING) ;

	}

	file_put_contents(CACHE[$section],json_encode($z));
}

public function clean_text( $text = '') {
	// removes spec chars and changes nl to br
	if (empty($text)) return '';
	$t = htmlspecialchars($text,ENT_QUOTES);
	$t = nl2br($t);
	return $t;
}

function get_curl ($src, $url,string $expected='',array $header=[]) {
		/* tries to geet the url, tests for suc cess and
			for expected result if supplied.
			returns result array on success
			returns false on erro.
		*/
		$curl = curl_init();
		$curl_options = $this->curl_options();
		curl_setopt_array($curl,$curl_options);
		curl_setopt($curl,CURLOPT_URL, $url);
		if ($header)
				curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		$aresp = [];
		$success=0;
		while (!$success) {
			static $tries =0;

			if ($tries > 2){
					echo "Can't get valid data from ext source  $src";
					u\echor($aresp,"Here's what I got for $src:");
					return false;
			}

			if (! $response = curl_exec($curl)) {
				$success = 0; echo "No curl resp on $src"; return false;
			}else { $success = 1;}


			if ($success && !$aresp = json_decode($response, true) ){
				$success = 0; echo " failed decode ";
			}else { $success = 1;}

			if ($success &&  $expected && !u\inMultiArray($expected,$aresp)) {
				$success = 0; echo "failed expected";
			}else { $success = 1;}

			if (! $success) {
				echo "Failed to get expected result from external site for $src. Try $tries. Retrying" . BRNL;
					++$tries;
					sleep (1);

			} else {

				curl_close($curl);
				return $aresp;
			}

		}

	}


} #end class
