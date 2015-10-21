<?php

// Import Dependencies
require __DIR__ . '/vendor/autoload.php'; 

// This is the user Controller, so define it as group User
$app->group('/api/v1.0/User', function() use ($app) {
	
	$app->get('/home', function () use ($app){ 
    	
    });//)->add($MIDDLEWARE_AUTH);
	
	$app->post('/login', function () use ($app){ 
    	
    });
	
	$app->delete('/logout', function () use ($app){ 
    	
    });
	
	$app->post('/register', function () use ($app){ 
    	
    });

});