<?php

// This is the group Controller, so define it as group Group
$app->group('/api/v1.0/Group', function() use ($app) {
	
	$app->post('/createGroup', function() use ($app){
		$emails = $app->request()->post('emails');
		$groupName = $app->request()->post('groupName');
		
		// Create the group details (name and date?)
		
		// Insert each account into the group
		
	});//)->add($MIDDLEWARE_AUTH);
	
});