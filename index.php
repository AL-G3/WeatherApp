<?php
// include('key.php');
require('key.php');

//function gets location data as a string from google api, then decodes the data to make it into an object to manipulate easier.
function get_location_data ($string) {
	$string = str_replace(' ', '+', $string);
	$string = preg_replace('/[^a-zA-Z0-9_ -]/s','', $string);
	//$location_data = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $string);
	$location_data = file_get_contents("http://api.zippopotam.us/us/$string");
	$location_data = json_decode($location_data);
	

	$output = (object) [
		'lat' => $location_data->places[0]->latitude,
		'lng' => $location_data->places[0]->longitude
	];

	return $output;
}
// function gets forcast from dark sky api 


function get_forecast($obj, $key) {
	$lat = $obj->lat;
	$lng = $obj->lng;

	$forecast = file_get_contents("https://api.darksky.net/forecast/$key/$lat,$lng");
	$forecast = json_decode($forecast);
	return $forecast;

}


// NON-FORM SUBMITTED STUFF

$triggered = "false";
$location = "''";
$error = "false";
$is_nice_out = "''";




if (count($_POST)) {
	// FORM SUBMITTED STUFF GOES HERE
	// https://maps.googleapis.com/maps/api/geocode/json?address=$location


	$triggered = "true";
	$location = $_POST["location"];
	$location_data = get_location_data($location);
	// var_dump($location_data);

  	if ($location_data == null) {
  		$error = "true";

  	} else {
		$forecast_data = get_forecast($location_data, $keys["dark_sky"]);
		$timezone_arr = explode('/', $forecast_data->timezone);
		$timezone = str_replace('_', ' ', $timezone_arr[1]);
		// $timezone = str_replace('_', ' ', $timezone);


		$current_forecast = (object) [
		  	'timezone'	=> $timezone,
			'temperature' => round($forecast_data->currently->temperature) . '&deg;' ,
			'summary' => $forecast_data->currently->summary,
			'wind_speed' => round($forecast_data->currently->windSpeed),
			'time' => date('l', $forecast_data->currently->time),
			'icon' => $forecast_data->currently->icon,
			'humidity' => $forecast_data->currently->humidity * 100 . '&percnt;'
			];


		$daily_forecasts = [];

		$count = 0;

		foreach ($forecast_data->daily->data as $day) {
			if ($count == 0) {
				$count++;
				continue;
			} else {
				$day_of_week = date('l', $day->time);

				$daily_forecast = (object) [
					'high' => $day->temperatureHigh,
					'low' => $day->temperatureLow,
					'icon' => $day->icon,
					'summary' => $day->summary,
					'humidity' => $day->humidity,
					'windspeed' => $day->windSpeed

				];


				// echo $day_of_week . "\n" . $daily_forecast->high . "\n" . $daily_forecast->low . "\n" . $daily_forecast->icon . "\n";

				$daily_forecasts[$day_of_week] = $daily_forecast;
			}
		}

		//var_dump($forecast_data->currently);
		//
		//foreach ($daily_forecasts as $day => $forecast) {
		//	echo "$day\n" . $forecast->high . "\n" . $forecast->low . "\n\n";
		//}

		if ($current_forecast->temperature > 82) $is_nice_out = "'It is MAD HOT out, Id stay inside if I were you'";
		elseif ($current_forecast->temperature < 65) $is_nice_out = "'It is too cold to smoke mad doinks'";
		else $is_nice_out = "'I got mad doinks! Let\'s smoke!!'";
  	}


	//if($current_forecast->temperature > 90 || $current_forecast->temperature < 30 )
		//echo "EXTREME WEATHER OUTSIDE: " . $current_forecast->temperature;



}




?>

<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv-"X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<meta name="keywords" content="weather, stoner weather, maybe baby, boats and also hoes">

	<title>Stoner Weather</title>

	<script src="https://cdn.jsdelivr.net/npm/vue"></script>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	







<!--CSS-->
	<style type="text/css">
		body {
			background-color: #ffffff;
			background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 1600 800'%3E%3Cg %3E%3Cpolygon fill='%23f3f3f3' points='800 100 0 200 0 800 1600 800 1600 200'/%3E%3Cpolygon fill='%23e8e8e8' points='800 200 0 400 0 800 1600 800 1600 400'/%3E%3Cpolygon fill='%23dcdcdc' points='800 300 0 600 0 800 1600 800 1600 600'/%3E%3Cpolygon fill='%23d0d0d0' points='1600 800 800 400 0 800'/%3E%3Cpolygon fill='%23c4c4c4' points='1280 800 800 500 320 800'/%3E%3Cpolygon fill='%23b9b9b9' points='533.3 800 1066.7 800 800 600'/%3E%3Cpolygon fill='%23adadad' points='684.1 800 914.3 800 800 700'/%3E%3C/g%3E%3C/svg%3E");
			background-attachment: fixed;
			background-repeat: no-repeat;
			background-position: center;
			background-size: cover,contain;
			font-family: 'Roboto Condensed', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
			margin: 0 auto;
			padding: 0;
			width: 100%;
		}

		main {
			min-height: 100vh;
			height: 100%;
		}

		form {
			max-height: 150px;
		}

		.custom-button,
		.location-input {
			box-shadow: 1px 3px 8px rgba(0, 0, 0, 0.3);
		}
		
		.custom-button {
			background: rgba(200, 50, 50, 1);
			border: 1px solid rgba(0, 0, 0, 0.25);
			border-top-right-radius: 4px;
			border-bottom-right-radius: 4px;
			color: #f0f1f5;
			padding: 0.3em;
			transition: background 200ms linear;
		}

		.custom-button:hover {
			background: rgba(175, 50, 50, 1);
			cursor: pointer;
		}

		.location-input {
			border: 1px solid rgba(0, 0, 0, 0.25);
			border-top-left-radius: 4px;
			border-bottom-left-radius: 4px;
			padding: 0.25em;
			max-width: 600px;
			width: 100%;
		}


		.centered-entire-screen-container {
			display: flex;
				align-items: center;
				flex-direction: column;
				justify-content: center;
			height: 100%;
			min-height: 100vh;
		}


		.current-forecast-card {
			display: grid;
				grid-auto-rows: auto;
				grid-template-columns: 1fr 1fr;
			max-height: 350px;
			margin: 1em auto;
			width: 50%;
			background-color: #71757a;
			color:#FFF;
;
		}

		.custom-shadow {
			box-shadow: 1px 3px 8px rgba(0, 0, 0, 0.3);
		}

		.daily-forecast-container {
			overflow-x: scroll;
  			overflow-y: hidden;
  			white-space: nowrap;
		}

		.daily-forecast-card {
			display: inline-block;
			height: 100%;
			max-height: 300px;
			width: 33%;
			background-color: #71757a;
			color:#FFF;
		}

		.current-details,
		.daily-details {
			list-style: none;
			text-align: center;
			padding: 0;
			margin: 0;
		}

		.current-details > li {
			padding: .25em;
			border-bottom: 1px solid rgba(255, 255, 255, .5)
		}

		.current-icon-container {
			display: flex;
				align-items: center;
				justify-content: center;
		}


		@media only screen and (max-width: 640px) {
			.current-forecast-card { width: 100%; }
			.daily-forecast-card {
				font-size: 0.9em; 
				width: 65%; 
				word-break: break-word;
			}
		}

		@media only screen and (max-width: 320px) {
			.daily-forecast-card {
				font-size: 0.666em;
			}
		}
	
	</style>


</head>
<body>
	<div id="app" class="container-fluid">


		<template v-if="triggered === true">
			<template v-if="error === true">
				<main class="centered-entire-screen-container">
					<p class="display-3">Oh no!</p>
					<p>There was a problem! Please try again.</p>
					<button class="btn btn-danger" v-on:click="triggered = false">Try Again</button>
				</main>
			</template>

			<template v-else>
				<nav class="bg-transparent text-center p-4">
					<span class="h1"><i class="fas fa-bong"></i>Stoner Weather <i class="fas fa-cannabis"></i></span>
				</nav>
				<main class="p-1 d-flex flex-column">
					<section class="current-forecast-card card text-center p-2 custom-shadow">
						<div class="current-icon-container">
							<?php if ($current_forecast->icon == 'clear-day') { ?>
								<p><i class="fas fa-5x fa-umbrella-beach"></i></p>
							<?php } elseif ($current_forecast->icon == 'rain') { ?>
								<p><i class="fas fa-5x fa-cloud"></i></p>
							<?php } elseif ($current_forecast->icon == 'snow') { ?>
								<p><i class="far fa-5x fa-snowflake"></i></p>
							<?php } elseif ($current_forecast->icon == 'wind') { ?>
								<p><i class="fas fa-5x fa-paper-plane"></i></p>
							<?php }  elseif ($current_forecast->icon == 'clear-night') { ?>
								<p><i class="fas fa-5x fa-moon"></i></p>
							<?php } elseif ($current_forecast->icon == 'fog' || $current_forecast->icon == 'cloudy' || $current_forecast->icon == 'partly-cloudy-day' || $current_forecast->icon == 'partly-cloudy-night') { ?>
								<p><i class="fas fa-5x fa-cloud"></i></p>
							<?php } else { ?>
								<p><i class="fas fa-5x fa-skull"></i></p>
							<?php } ?>
						</div>

						<ul class="current-details">
							<li><?php print $current_forecast->summary;  ?></li>
							<li>Temperature: <?php print $current_forecast->temperature; ?></li>
							<li><?php print $current_forecast->timezone; ?></li>
							<li><?php print $current_forecast->time; ?></li>
							<li>Wind Speed:  <?php print $current_forecast->wind_speed; ?><abbr title="miles per hour"> MPH</abbr></li>
							<li>Humidity: <?php print $current_forecast->humidity;?></li>
							<li><em>{{isNiceOut}}</em></li>
						</ul>
					</section>

					<section class="daily-forecast-container">
						<?php foreach ($daily_forecasts as $day => $forecast) { ?>
						<div class="daily-forecast-card card p-4 text-center custom-shadow">
							<!--icon-->
							<?php if ($forecast->icon == 'clear-day') { ?>
							<p><i class="fas fa-3x fa-umbrella-beach"></i></p>
							<?php } elseif ($forecast->icon == 'rain') { ?>
							<p><i class="fas fa-3x fa-cloud"></i></p>
							<?php } elseif ($forecast->icon == 'snow') { ?>
							<p><i class="far fa-3x fa-snowflake"></i></p>
							<?php } elseif ($forecast->icon == 'wind') { ?>
							<p><i class="fas fa-3x fa-paper-plane"></i></p>
							<?php }  elseif ($forecast->icon == 'clear-night') { ?>
							<p><i class="fas fa-3x fa-moon"></i></p>
							<?php } elseif ($forecast->icon == 'fog' || $forecast->icon == 'cloudy' || $forecast->icon == 'partly-cloudy-day' || $forecast->icon == 'partly-cloudy-night') { ?>
							<p><i class="fas fa-3x fa-cloud"></i></p>
							<?php } else { ?>
							<p><i class="fas fa-3x fa-skull"></i></p>
							<?php } ?>

							<!--title-->
							<p class="daily-forecast-card__title "><?php print $day; ?></p>

							<!--temps-->
							<ul class="daily-details">
							<li><?php print $forecast->summary;  ?></li>
							<li>Highs/Lows: <?php print round($forecast->high) . '&deg;' . ' / ' . round($forecast->low) . '&deg;'; ?></li>
							<li>Humidity: <?php print ($forecast->humidity) * 100 . '&percnt;';  ?></li>
							<li>Wind Speed: <?php print round($forecast->windspeed);  ?> MPH</li>
							</ul>
						</div>
						<?php } ?>
					</section>
				</main>
			</template>
		</template>

		<template class="form_sub" v-else>
			<main class="d-flex align-items-center justify-content-center flex-column">
				<header>
					<h1 class="text-center display-4"><i class="fas fa-bong"></i>Stoner Weather <i class="fas fa-cannabis"></i></h1>
					<p class="text-center">Please enter your zip code</p>
				</header>

				<form method="post" name="search" class="container">
					<div class="form-group d-flex justify-content-center">
						<input class="location-input" type="text" name="location">

						<button class="custom-button" type="submit">Search</button>
					</div>
				</form>
			</main>
		</template>
<!-- this is where the card styling will go -->

		<script>
			const app = new Vue({
				el: '#app',
				data: {
					triggered: <?php echo $triggered; ?>,
					location: '',
					error: <?php echo $error; ?>,
					isNiceOut: <?php echo $is_nice_out; ?>
				}
			})
		</script>

	</div>
</body>
</html>