<?php

//$app->group('/api/v1.0/Calendar', function() use ($app) {
	$app->get('/test', function () use ($app){
		
		//require_once(__DIR__.'/../../../models/Google_Calendar.php');

		$test = new Google_Calendar();
				
		
	});
//});