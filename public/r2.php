<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	require $_SERVER['DOCUMENT_ROOT'] . '/init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;
	$Plates = $container['plates'];
	$Defs = $container['defs'];

	define('stop',true);
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






if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if (params('admin')){
	// first use input form to get latest info
		$y = prepare_admin();
		u\echor ($y ,'prepped for form', stop);
		echo $Plates->render('admin', $y);

	} else {
		$y = prepare_today();
		u\echor ($y ,'prepped for today', stop);
		echo $Plates->render('today', $y);

		exit;
	}
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// post data frtom form

	post_admin ($_POST);

	$sections = ['info','weather','air',',light','camps','fire','calendar'];
	// local section updated by post form

	//update_sections ($sections);
} else {
	die ("Unknown request method");
}




###############################
function prepare_admin() {
// get sections needed for the admin form
	foreach (['info','camps','calendar','fire'] as $section) {
		$y[$section] = get_section ($section);

	}

	// build dynamic content (options ,etc)

	return $y;
}


 function prepare_today() {
 // get sections needed
	foreach (['today','camps','calendar','fire','light','air','uv','weather'] as $section) {
		$y[$section] = get_section ($section);
	}
u\echor($y, 'y array for today',stop);
	return $y;
 }


function post_admin ($post) {
 // insert posted data and dependencies into cacjes
 // l
// 	$y=[];
// 	 $dtu = new \DateTime();
// 	$dtu->setTimezone(new \DateTimeZone('America/Los_Angeles'));
// 	$y['updated'] = $dtu->format("d M H:i");
// 	$dtt = \DateTime::createFromFormat("m/d/Y",$tdate);
// 	 $y['target'] = $dtt->format("l, d M Y");
u\echor ($post, 'Posted',false);

//  info cache
	$y=[];
	$y['announcements'] = $post['announcements'];
	//$y['today'] = $post['target'];
	$y['updated'] = date('d M H:i');

	update_section('info',$y);

// weather (also does light
	$y=[];
	$y['weather_warn'] = $post['weather_warn'];
	update_section('weather',$y);

// fire
	$y=[];
	$fire = $post['fire_level'];
	$y['fire_level'] = $fire ;
	$fire_levels = array_keys(Defs::$firewarn);
	$y['fire_options'] = u\buildOptions($fire_levels,$check=$fire);
	update_section('fire',$y);

// camps
	$y=[];
	$y = $post['cg'];
	foreach (array_keys(Defs::$campsites) as $cgcode){
		$opt = u\buildOptions(Defs::$cgavail, $y['cgavail'][$cgcode]);
		$y['cgoption'][$cgcode]  = $opt;
	}
	u\echor ($y, 'camps', false);
	update_section('camps',$y);

	$y=[];
	$y = $post['cg'];
	update_section('camps',$y);

// calendar
	$y = [];
	foreach ($post['calendar']['event_datetime'] as $i=>$edt) {
		if (empty($edt)) continue;
		$y['calendar']['event_datetime'][$i] = check_dt($edt);
		foreach (['event_location','event_type','event_title','event_note'] as $evd) {

			$y['calendar'][$evd][$i] = $post['calendar'][$evd][$i];
		}
	$y=[];
	//fire


	}

	update_section('calendar',$y);

	exit;
}

#-----------   SUBS ------------------------------

function get_section($section) {
	// returns cache for section with dynamic data added.

		if (! file_exists (CACHE[$section])) {
			$y = load($section,true); //true = add init data
		}

		$y = json_decode (file_get_contents(CACHE[$section]), true);

		// add dynamic

		return $y;
}

function load_section (string $section , bool $init=false) {
	$v = array ('updated' => $date);
	// external
			switch ($section) {
				case 'weather': $w = external_weather();break;
				case 'air': $w = external_airqual_2(); break;
				case 'light': $w = external_light(); break;
				default: $w = [];
		}

	// internal
		$x = internal($section) ?? [];
	//init
	if ($init)
		$y = init($section) ?? [];

	$z = array_merge($v,$w,$x,$y);
	file_put_contents(CACHE[$section],json_encode($z));
	return $z;
}

function update_section($section,$u) {
		// reads section cache
		// reloads from source if necessary
		// merges in $u
		// sves to cache


		if (!file_exists(CACHE[$section])){

		}



		$z = array_merge($y,$u);
		file_put_contents(CACHE[$section],json_encode($y));
		return true;
	}



########   LOAD ###############


function internal ($section) {
	$y['fire'] = array
		(
		'firewarn'	=>	$Defs -> fire_warn($fire),
		'firecolor'=> $Defs -> scale_color($fire),
		);

	$y['camps'] = array
		(
		'sitenames' => Defs::$sitenames,
		'cgsites' => Defs::$campsites,
		'cgfeatures' => Defs::$campfeatures,

		);





	return $y[$section];
}

#-----------------  LOAD EXTERNASL --------------------



function external_airqual (){
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

function external_airqual_2 (){
// uses api to get aq at jumbo rocks (pinto wye).

	global $Defs;

[$lat,$lon] = split_coord('jr');
$url = "http://api.openweathermap.org/data/2.5/air_pollution?lat={$lat}&lon={$lon}&appid=" . OPENWEATHERMAP_KEY;
// echo $url; //exit;


//echo "Updating air quality" . BRNL;
	$curl = curl_init();
	$curl_options = curl_options();
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
		$aqi_scale = $Defs->aq_scale($aqi);
		$aqi_color = $Defs->scale_color($aqi_scale);

		$y['aqi'] = $aqi;
		$y['pm10'] = $aresp['list'][0]['components']['pm10'];
		$y['o3'] = $aresp['list']['0']['components']['o3'];
		$y['aqi_scale'] = $aqi_scale;
		$y['aqi_color'] = $aqi_color;
	}

	$z['air'] = $y;

	update_section('air',$z);

 }


function external_weather ($locs) {
	global $Defs;

	$curl = curl_init();
	$curl_options = curl_options();
	curl_setopt_array($curl,$curl_options);

// get forecast data for each location
	foreach ($locs as $loc) {

		$url = 'http://api.weatherapi.com/v1/forecast.json?key=' . WEATHERAPI_KEY . '&q='. Defs::$coordinates[$loc] . '&days=3&aqi=yes&alerts=yes';

		curl_setopt($curl, CURLOPT_URL,$url);
		$resp = curl_exec($curl);
		$err = curl_error($curl);
		if ($err) {
			echo "cURL Error #:" . $err;
			exit;
		}
		curl_close ($curl);
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
				'High' => round($x['day']['maxtemp_f']),
				'Low' => round($x['day']['mintemp_f']),
			//	'winddir' => $x['day']['winddir'],
				'avghumidity' => $x['day']['avghumidity'],
				'maxwind' => round($x['day']['maxwind_mph']),
				'Skies' => $x['day']['condition']['text'],
				'Rain' => $x['day']['daily_chance_of_rain'],
				// saved to get uv and light
				'UV' => $x['day']['uv'],

				'Sunrise' => time_format( $x['astro']['sunrise']),
				'Sunset' => time_format($x['astro']['sunset']),
				'Moonrise' => time_format($x['astro']['moonrise']),
				'Moonset' => time_format($x['astro']['moonset']),
				'Moon Phase' => $x['astro']['moon_phase'],
				);
		}
	} #end foreach

	return $y;
}


function external_uv() {
	// $curl = start_curl ("https://api.openuv.io/api/v1/uv?lat=34.12&lng=-116.14&dt=2018-01-24T10:50:52.283Z' -H 'x-access-token: f9792ff531cddbab6b41fb6302e7c256'"
// 	$response = curl_exec($curl);
// 	$err = curl_error($curl);
// 	curl_close($curl);
//
// 	if ($err) {
// 		echo "cURL Error #:" . $err;
// 		return;
// 	}
	$uv = 7;
	//'UV' => $z['jr']['0']['UV'],

	$uv_data = uv_data($uv);

		$y = array
		(
			'uv' => $uv_data[0],
   		'uvscale'  => $uv_data[1],
   		'uvwarn'  => $uv_data[2],
   		'uvcolor' => $Defs->scale_color($uv_data[1]),
		);
	return $y;
}

function load_light ($z) {
	// $z is an array containing the fields listed (e.g. weather)
	global $Defs;
	// set up air/light based on today at jr
   $light = array(
   	'UV' => $z['jr']['0']['UV'],
   	'uvscale'  => $z['jr']['0']['uvscale'],
   	'uvwarn'  => $z['jr']['0']['uvwarn'],
   	'uvcolor' => $Defs->scale_color($z['jr']['0']['uvscale']),
   	'sunrise'	 => $z['jr']['0']['Sunrise'],
   	'sunset' => $z['jr']['0']['Sunset'],
   	'moonrise'  => $z['jr']['0']['Moonrise'],
   	'moonset'  => $z['jr']['0']['Moonset'],
   	'moonphase' => $z['jr']['0']['Moon Phase'],
   );
   return $light;
}



// -----------  INIT -------------
function init ($section) {
	// return $y['section']



	$y['fire'] = array
			(
				'fire_level' => 'Low'
			);


		$y['calendar'] = array
			(
				'event_datetime' => array
                (
                    '0' => 'April 1 8:00pm',
                    '1' => 'Tomorrow 10:00 am',
                    '2' => 'mar 15 18:00',
                ),

            'event_location' => array
                (
                    '0' => 'Indian Cove amphitheater',
                    '1' => 'Discovery Trail trailhead',
                    '2' => 'Joshua Tree Cultural Center,'
                ),

            'event_type' => array
                (
                    '0' => 'Ranger Talk',
                    '1' => 'Walk and Talk',
                    '2' => 'Ranger Talk',
                ),

            'event_title' => array
                (
                    '0' => 'Adaptations to the Desert',
                    '1' => 'How these rocks got here',
                    '2' => 'tbd',
                ),

            'event_note' => array
                (
                    '0' => '',
                    '1' => 'gather at crosswalk on Park Drive',
                    '2' => '',
                ),
			);
		$y['camps'] = array
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
				);
			$y['today'] = array
				(
					'target' => 'July 3, 2022',
					'updated' => '2 Jul 21:55',
					'fire_level' => 'High',
					'pithy' => 'Something Pithy',
					'cg' =>  $y['camps'],
					'sitenames' => Defs::$sitenames,
					'announcements' => '40 Palms Canyon is closed for the summer.',
					'weather_warn' => 'Heat Warning!',
					'calendar' => $y['calendar'],

				);

      return $y[$section];
   }



// -----------   UTILITY FUNCTIONS -------------

function time_format($time) {
	// remove leading 0

	if (substr($time,0,1) == '0'){
		$time = substr ($time, 1);
	}

	return $time;
}

function curl_options () {

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




function uv_data($uv) {
	// takes numeric uv, returns array of uv, name, warning
	global $Defs;
			$uvscale = $Defs->uv_scale($uv);
			$uvwarn = $Defs->uv_warn($uvscale);
			return ([$uv,$uvscale,$uvwarn]);
}


function page_start() {

	$text = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <link rel='stylesheet' href = '/today.css' />
	<title>Today in Joshua Tree National Park</title>
</head>
<body>
EOF;
	return $text;
}

function split_coord ($loc) {
	$coord = Defs::$coordinates[$loc];
	[$lat,$long] = explode(',',$coord);
	return [$lat,$long];
}

function over_cache_time($section) {
	//global $Defs;
	$filetime = filemtime(CACHE[$section] );
	$limit = Defs::$cache_times[$section] * 60;
	if ($time() - $filetime > $limit) return true;
	return false;
}

function check_dt($edt) {
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

