<?php

//$app->group('/api/v1.0/Calendar', function() use ($app) {
	$app->get('/test', function () use ($app){
		
		//require_once(__DIR__.'/../../../models/Google_Calendar.php');
		//require_once(__DIR__.'/../../../../quickstart.php');

		echo "call";

		$test = new Google_Calendar();
		$test->testCal();
		//if($test->getButton() == true)
			//$test->test2();
		//$test->requestCalendar();
		//echo "good"
		
		
	});
//});