<?php

// This is the meeting Controller, so define it as group Meeting
$app->group('/api/v1.0/Meeting', function() use ($app, $AUTH_MIDDLEWARE) {
	
	$app->post('/rsvp', $AUTH_MIDDLEWARE(), function() use ($app){
		$rsvpToken = $app->request->post('rsvpToken');
		
		// modify attendie entry to acception
		
	});
	
});