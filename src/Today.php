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

	$this-> all_sections = ['info','weather','air','camps','fire','calendar'];
	// locations to use for weather report
	$this -> wlocs = ['jr','hq','cw','br'] ;
	$this -> airlocs = ['jr','cw'];

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

	echo "Pages updated" . BRNL;
}

public function prepare_today($force=false) {
 //set force true or false to force cache updates
 // get sections needed

// alerts included below onloy to trigger timely updates.  not used in today
	foreach (['weathergov','weather','air','uv','calendar','light','alerts'] as $section) {
		$y[$section] = $this -> load_cache ($section, $force);
	}
	$y['today'] = $this -> load_today();
	$v = file_get_contents(REPO_PATH . "/data/version") ;
//	echo 'Version: ' . $v . BRNL; exit;
	$y['version'] = $v;

// u\echor($y, 'y array for today');
	//clean text for display (spec chars, nl2br)
		foreach(['pithy','fire_warn','weather_warn','announcements'] as $txt){
	// clean text in divs (
		$y['today'][$txt] = $this->clean_text($y['today'][$txt]);
	}
	// tweak some styles
	$y['fire']['firecolor'] = $this->Defs->scale_color($y['today']['fire_level']) ;
	$y['fire']['firelevel'] = $y['today']['fire_level'] ;




//   u\echor($y, 'y array for today', STOP);
	return $y;
 }

public function prepare_admin() {
// get sections needed for the admin form

	$y = $this -> load_today();
	$y['alerts'] = $this->load_cache('alerts');

// 	u\echor($y, 'y in prepare admin', STOP);


$fire_levels = array_keys(Defs::$firewarn);
	$y['fire_level_options'] = u\buildOptions($fire_levels,$y['fire_level']);



// camps

	foreach (array_keys(Defs::$campsites) as $cgcode){
		$opt = u\buildOptions(Defs::$cgavail, $y['camps']['cgavail'][$cgcode]);
			$opts[$cgcode]  = $opt;
	}

	$y['camps']['cg_options'] = $opts;

	$y['calendar'] = $this->load_cache('calendar');


	//u\echor ($y, 'Y to admin',NOSTOP);
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
	$y['pithy'] = u\despecial($post['pithy']);
	$y['fire_warn'] = $post['fire_warn'];
	$y['fire_level'] = $post['fire_level'];

	$y['weather_warn'] = $post['weather_warn'];



	$y['camps'] = $post['cg']; // array

	$this -> write_cache('today',$y);


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

//	u\echor($x,'c2',STOP);
	$this->write_cache('calendar',$x);
	// remove empyt and expired entries
	$this->refresh_cache('calendar');


	// rebuild the pages
	$this->rebuild();

//	u\echor ($z,'Calendar to cache');


}

#-----------   SUBS ------------------------------



public function load_cache ($section,bool $force=false) {
		$refresh = $force;

		if (! file_exists (CACHE[$section])) {
			$refresh = true;
		} else {
			$mtime = filemtime (CACHE[$section]);
			$maxtime = $this->Defs->getMaxtime ($section) ;
			// $maxtime set to 0 if cache is maintanined elswhere,
			// by admin or by resetting another cache.
			$diff = time() - $mtime;
			if ($maxtime && ( $diff > $maxtime )){
					$refresh = true;
					echo "timeout on $section cache. max $maxtime; is $diff." . BRNL;
			}
		}
		if ($section == 'calendar'){$refresh = true;}

// 			echo "load $section cache: refresh " , ($refresh)?'true':'false' , BRNL;
		if ($refresh) {
			$this->refresh_cache($section);
		}

		$y = json_decode (file_get_contents(CACHE[$section]), true);
// 	if ($section == 'weathergov'){u\echor ($y);}
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
				case 'weather':
					$w = $this -> external_weather($this->wlocs) ;
						$this->write_cache ($section,$w);
					$lt = $this -> internal_light ($w) ;
						$this->write_cache ('light',$lt);
					$uv = $this -> internal_uv ($w) ;
						$this->write_cache ('uv',$uv);
					$alerts = $this-> weather_alerts($w);
						$this->update_alerts(['weather' => $alerts]);


					break;
				case 'air': $w = $this -> external_airqual_2($this->airlocs) ?? [];
					$this->write_cache ($section,$w);
					break;

				case 'calendar' :
					$w = $this->filter_calendar();
					$this -> write_cache($section,$w);
					break;

				case 'properties':
					$plocs = ['jr','cw','hq','br','kv'];
					$w = $this->set_properties($plocs);
					if (!$w)die ("no properties");
					break;
				case 'weathergov':
					$w = $this -> external_weathergov ($this->wlocs) ;
					$this -> write_cache($section,$w);
	//u\echor($w,'gov weather',STOP);
					break;
				case 'alerts':
					$alerts = $this-> external_alerts();
					$this->update_alerts(['weathergov' => $alerts]);
					break;
				case 'today':
				default: return true;
		}

	return true;
}

private function filter_calendar() {
	/*
		removes expired events from calendar
		calenar = array (
			0 = array (dt,type,title,location,note),
			1 = ...
			);
	*/

	$z=[];
	if (!file_exists(CACHE['calendar'])) {
		$y = self::$dummy_calendar;
	} else {
		$y = json_decode (file_get_contents(CACHE['calendar']), true);;
		if (empty ($y)){
			$y = self::$dummy_calendar;
		}
	}
// 	u\echor($y,'cal loaded');
	// ignore invalid dt or dt older than now
	// set first term in if to 1 to prevent filtering
	foreach ($y as $cal){
		if ( 1 || (is_numeric($cal['dt']) && (time() < $cal['dt']) )){
			$z[] = $cal;
		}
	}
// 		u\echor($z,'cal filtered', STOP);
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
// 	u\echor($y,'loaded today');



// u\echor($y,'today after clean', STOP);

		$target_date = date('l, d M Y');
		$y['target'] = $target_date;
		$y['updated'] = date ('d M H:i');

		return $y;




}

#-----------------  LOAD EXTERNASL --------------------



public function external_airqual (){
	$section = 'air';
	$loc = 'jr';
	$source = 'air-quality.p.rapidapi.com/current/';
	$y = [];
	$y['src'] = $source;

	// external data
// uses api to get aq at jumbo rocks (pinto wye).

	$curl = curl_init();
	$curl_options = $this->curl_options();
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
	u\echor($aresp, "$loc from $source" ,STOP);
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

public function external_airqual_2 (array $locs=['jr']){
	$source = 'api.openweathermap.org';
	$y = [];
	$y['src'] = $source;

// uses openweathermap.org
	$locs = ['jr','cw','br'];
	$curl = curl_init();
	$curl_options = $this -> curl_options();
		curl_setopt_array($curl,$curl_options);


	foreach ($locs as $loc) {

	[$lat,$lon] = $this -> split_coord($loc);
	$url = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$lat}&lon={$lon}&appid=" . Defs::$api_keys['openweathermap'];
	// echo $url; //exit;


	//echo "Updating air quality" . BRNL;

		curl_setopt($curl,CURLOPT_URL, $url);

		$response = curl_exec($curl);
		$err = curl_error($curl);


		$aresp = json_decode($response, true);
// u\echor($aresp, "$loc from $source" );
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
			$y['dt'] = $aresp['list']['0']['dt'];
		}

		$x[$loc] = $y;
	}
//u\echor($x,'retrieved airq data:');
	echo "External airqual_2 updated from openweathermap.org" . BRNL;

	curl_close($curl);
	return $x;

 }

public function external_airqual_3 (array $locs=['jr']){
	$source = 'www.airnowapi.org/aq/observation';
	$y = [];
	$y['src'] = $source;
/* uses airnow.org - referred from eps.gov
	current is good, but forecasts return empty.
	forecast at airnowapi.org/aq/forecast
	now at aq/observation/latlong/current


*/
	$locs = ['jr','cw','br'];
	$curl = curl_init();
	$curl_options = $this -> curl_options();
		curl_setopt_array($curl,$curl_options);


	foreach ($locs as $loc) {

	[$lat,$lon] = $this -> split_coord($loc);

	$url = "https://www.airnowapi.org/aq/observation/latLong/current/?format=application/json&latitude=$lat&longitude=$lon&distance=25&API_KEY=" . Defs::$api_keys['airnow'];
	// echo $url; //exit;

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

	//echo "Updating air quality" . BRNL;

		curl_setopt($curl,CURLOPT_URL, $url);

		$response = curl_exec($curl);
		$err = curl_error($curl);


		$aresp = json_decode($response, true);
u\echor($aresp, "$loc from $source" ,STOP);
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
			$y['dt'] = $aresp['list']['0']['dt'];
		}

		$x[$loc] = $y;
	}
//u\echor($x,'retrieved airq data:');
	echo "External airqual_3 updated" . BRNL;

	curl_close($curl);
	return $x;

 }


private function external_weather ($locs) {


// get forecast data for each location
	foreach ($locs as $loc) {
	$ch = curl_init();
	curl_setopt_array($ch,$this -> curl_options() );
		$url = 'http://api.weatherapi.com/v1/forecast.json?key=' . Defs::$api_keys['weatherapi'] . '&q='. Defs::$coordinates[$loc] . '&days=3&aqi=yes&alerts=yes';

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
//   u\echor($aresp , 'weather response',STOP);


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
				'moonillumination' => $x['astro']['moon_illumination'],
				'moonphase' => $x['astro']['moon_phase'],

				);
		}
		$walerts = $aresp['alerts']['alert'];
		foreach ($walerts as $alertno => $ad){

			/*

			 [headline] => AIRNow Program, US Environmental Protection Agency
			 [msgtype] =>
			 [severity] =>
			 [urgency] =>
			 [areas] =>
			 [category] => Air quality
			 [certainty] =>
			 [event] => Ozone is forecast to reach 166 AQI - Unhealthy on Tue 07/12/2022.
			 [note] =>
			 [effective] => 2022-07-12T08:00:00+00:00
			 [expires] => 2022-07-13T08:00:00+00:00
			 [desc] => Ozone is forecast to reach 166 AQI - Unhealthy on Tue 07/12/2022.
			 [instruction] =>
			 */

			 $y['alerts'][] =$ad;
		}
	} #end foreach
	echo "External weather updated" . BRNL;
// 	u\echor ($y,'weather array', STOP);
	return $y;
}

public function external_weathergov ($locs) {
	//uses weather.gov api directly

// get forecast data for each location
	$ch = curl_init();
	curl_setopt_array($ch,$this -> curl_options() );

	foreach ($locs as $loc) {
	// get forecast url from properties file
		$url = $this->properties[$loc]['forecast'];
		if (! $url){
			trigger_error("no url for location $loc", E_USER_WARNING);
			continue;
		}
		curl_setopt($ch, CURLOPT_URL,$url);
		$resp = curl_exec($ch);
		$err = curl_error($ch);
		if ($err) {echo "cURL Error #:" . $err;exit;}

	// convert ot php arrays
		$aresp = json_decode($resp, true);
// u\echor($aresp , 'weathergov response');

		while (! $data = $aresp['properties'] ){
			static $tries =0;
			echo "No properties in weather.gov aresp.  Tries $tries. Retrying" . BRNL;
			// u\echor($aresp,'aresp');
			// retry
			if ($tries > 2){
				echo "Can't get weather.gov";
				u\echor($aresp,'recvd response from forecast');
				exit;
			}
			$resp = curl_exec($ch);
			$aresp = json_decode($resp, true);
			++$tries;
		}
			;
		$y=[];
		foreach ($data['periods'] as $p){ // period array]	d
			// two periods per day, for day and night
			// put into one array
// u\echor($p,'period',NOSTOP);
	// set day (key) to datestamp for day, not hours
			$sttime = $p['startTime'];
			$daytext = date('Y-m-d',strtotime($sttime));
			$day = strtotime($daytext);
//echo "st: $sttime; dt: $daytext; day:$day" . BRNL;
			// start array for this day


			$pname = date('d',strtotime($p['name']));

			if ($p['isDaytime']) {
					$y[$day]['day'] = array (
						'temp' => $p['temperature'],
						'wind' => $p['windSpeed'] . ' ' . $p['windDirection'],
						'image' => $p['icon'],
						'forecast' => $p['shortForecast'],
					);
			} else {
					$y[$day]['night'] = array (
						'temp' => $p['temperature'],
						'wind' => $p['windSpeed'] . ' ' . $p['windDirection'],
						'image' => $p['icon'],
						'forecast' => $p['shortForecast'],
					);
			}
		}
		$z[$loc] = $y;
		$y=[];
	} #end foreach
	curl_close ($ch);
	echo "External weathergov updated" . BRNL;
	return $z;
}

public function external_alerts () {
	//uses weather.gov api directly

// get forecast data for each location
	$ch = curl_init();
	curl_setopt_array($ch,$this -> curl_options() );


	// get forecast url from properties file
		$url = 'https://api.weather.gov/alerts/active/zone/CAZ230';
		if (! $url){
			trigger_error("no url for location $loc", E_USER_WARNING);
			die('died in alerts');
		}
		curl_setopt($ch, CURLOPT_URL,$url);
		$resp = curl_exec($ch);
		$err = curl_error($ch);
		if ($err) {echo "cURL Error #:" . $err;exit;}

	// convert ot php arrays
		$aresp = json_decode($resp, true);
//  u\echor($aresp , 'weather.gov alert response');

		if (! $data = $aresp['features'] ){
			echo "No alerts";
			return [];
		}
			;
		$y=[];
		foreach ($aresp['features'] as $alert){
			if (empty($alert)){continue;}
			$aprop = $alert['properties'];
			$exp = strtotime($aprop['expires']);


			$y = array
			(
				'cat' => $aprop['category'],
				'event'  => $aprop['event'],
				'desc'  => $aprop['description'],
				'instruction' => $aprop['instruction'],
				'expires' => $exp,
			);

// 		u\echor ($y,'uv',STOP);
			$z[] = $y;
		}
	curl_close ($ch);
	echo "External alerts updated" . BRNL;
	return $z;
}

public function set_properties ($locs) {
	// gets meta data for each location by lat,lon
	// saves it in data file properties.json

// get forecast data for each location
	foreach ($locs as $loc) {
echo "Starting on $loc" . BRNL;
	$ch = curl_init();
	curl_setopt_array($ch,$this -> curl_options() );
	$coords =  Defs::$coordinates[$loc];
	if (!$coords){die ("No coordinates for loc $loc");}
		$url = 'https://api.weather.gov/points/' . $coords;
//(https://api.weather.gov/points/{lat},{lon}).

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
//	u\echor ($aresp , "Properties for $loc",NOSTOP);

		if (! $props = $aresp['properties'] ){// array
			trigger_error("no properties in response for $loc", E_USER_WARNING);
			continue;
		}
		$y[$loc] = $props;
	} #end foreach

	$this->write_cache('properties',$y);
	echo "Properties updated" . BRNL;
	return true;


}

public function weather_alerts($w=[]) {
	// retrieve alerts from the weather report
if (empty($w)){
		$w = $this->load_cache('weather');
	}
	$alerts = $w['alerts'] ?? [];
		foreach ($alerts as $alert) {


			$y = array
			(
				'cat' => $alert['category'],
				'event'  => $alert['event'],
				'desc'  =>$alert['desc'] ?? '',
				'instruction' => $alert['instruction'] ?? '',
				'expires' => $alert['expires'] ?? ''
			);
		}


		$z[] = $y;
// 		u\echor ($z,'alert from weatherapi.com');
	return $z;
}

public function update_alerts(array $y=[]) {
	/* updates alert cache with y
		 y is array of alerts
		 y = source => array(
		 		array(expires=>date, event=> ...

		 routine replaces source in alert cache

	*/
	// don't use load_cache, because it will send you into loop
	$alerts = json_decode(REPO_PATH . 'alerts.json',true) ;
// 	u\echor($alerts,'loadedd from alert cache');
// 	u\echor ($y,'incoming array');

	$newset=[];
	foreach ($y as $source => $alertset){
		foreach ($alertset as $alert) {
			$newa = $this->fix_alert_expiry($alert);
			if ($newa){$newset[] = $newa;}
		}
		$alerts[$source] = $newset;
	}
// 	u\echor($alerts,'merged');
	$this->write_cache('alerts',$alerts);

	return true;

}

private function internal_uv($w=[]) {

// retrieve uv from the weather report
if (empty($w)){
		$w = $this->load_cache('weather');
	}


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

// 		u\echor ($y,'uv',STOP);
	return $y;
}

private function internal_light ($w=[]) {

// retrieve uv from the weather report
	if (empty($w)){
		$w = $this->load_cache('weather');
	}


	$y['sunrise']  = $w['jr']['0']['sunrise'];
	$y['sunset']  = $w['jr']['0']['sunset'];
	$y['moonrise']  = $w['jr']['0']['moonrise'];
	$y['moonset']  = $w['jr']['0']['moonset'];
	$y['moonphase']  = $w['jr']['0']['moonphase'];
	$y['moonillumination'] =  $w['jr']['0']['moonillumination'];

	$y['moonimage'] = '/images/moon/' . $this->Defs->getMoonPic($y['moonphase']);

// 	u\echor($y,'l and d');
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
			$uvwarn = $this ->Defs->uv_warn($uvscale);
			return ([$uv,$uvscale,$uvwarn]);
}

public function start_page ($title = 'Today in the Park',$pcode='') {
	$scbody = '';
	$scstyle = '';
	if ($pcode=='s') {$scbody='onLoad="pageScroll()"';
		$scstyle = "<style>html {scroll-behavior: smooth;}</style>";
	}
	if ($pcode=='p'){
		$scbody = "onLoad='startRotation(10)'";
	}

	$text = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <link rel='stylesheet' href = '/today.css' >
	<title>$title</title>
	<script src='/js/snap.js'></script>
	$scstyle

</head>
<body $scbody>


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

private function fix_alert_expiry($alert){
	if (empty($alert['expires'])) {
				// no expiry date.  set for 3 days
				$alert['expires'] = strtotime(' + 3 day');
				echo "alert expiry set to + 3 days on alert" . $alert['event'];
		}
		elseif (is_numeric ($alert['expires'])){
			# do nothing.  it's ok
		}
		elseif ($exp=strtotime($alert['expires'])){
			$alert['expires'] = $exp;
		} else {
				die ("Cannot get expire date on alert" . $alert['event']);
		}
		// remove if expired
		if (time() > $alert['expires'])  {return [];}
		return $alert;

}


} #end class
