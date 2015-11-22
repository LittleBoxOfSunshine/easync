<?php

// This is the meeting Controller, so define it as group Meeting
$app->group('/api/v1.0/User', function() use ($app) {
	
	$app->post('/rsvp', function() use ($app){
		$rsvpToken = $app->request->post('rsvpToken');
	});//)->add($MIDDLEWARE_AUTH);
	
});