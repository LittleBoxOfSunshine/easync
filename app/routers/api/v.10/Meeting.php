<?php

$app->group('/api/v1.0/Meeting', function() use ($app, $AUTH_MIDDLEWARE) {
	
	//for giving list of meeting possibilities
	$app->post('/planMeeting', $AUTH_MIDDLEWARE(), function () use ($app){
		
		/*

		required:
			startTime, endTime, name, creatorUserID (global, not input), timeZone
		
		json object consists of:
			equireds ^ 
			array of emails
			length
			day range (how early/late in day)
			all attendees required(bool)

		output: array of the ranges, sorted by rank;# of people can't attend each one
		*/

		/*
		steps: (a lot of these will be combined/moved)
			copy emails, prune those w/out googlecal acess
			load any tokens, construct any API objects
			pull events
			diff by all attendees - look up group scheduling algorithm 
				if impossibr and required
					give failure message
				else if not required
					move on
			diff, rank by max #tendies
			give top options or failure if no optoins exist (store copy of results in session)
			
		*/

		$users = json_decode($app->request()->getBody());
		$emails = $users->emails;

		//get non null googAuthToken from user, get calId with token from CalendarTokens
		/*

		$stmt = Database::prepareAssoc("SELECT `token` FROM `CalendarTokens` WHERE userID=:userID AND platformID=:platformID");
		$stmt->execute();
		$calToken = $stmt->fetch();
		*/

	});

	//if exists, update rather than insert
	$app->post('/finalMeeting', $AUTH_MIDDLEWARE(), function () use ($app){
		/*
		input: index of time option chosen
		output: success/failure msgs

		steps:
			load meeting details from session using index (time range) given as input
			create meeting (insert db)
			add attendees (insert db)
			send confimartion/rsvp emails to list of emails
			echo successs/failure
		*/
	});

/*
delete this!!!!!!
*/
	$app->post('/addContacts', $AUTH_MIDDLEWARE(), function() use ($app){
		global $USER_ID;
		$contacts = json_decode($app->request()->getBody());
		$contacts = $contacts->emails;
		
		Database::beginTransaction();

		$stmt = Database::prepareAssoc("INSERT INTO Contacts (`userID`, `contactEmail`) VALUES (:userID, :contactEmail);");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':contactEmail', $contact);
		
		foreach($contacts as $contact)
			$stmt->execute();
			
		Database::commit();
		
		if($stmt->errorCode() === '00000'){
			echo 'Contacts Added';
		}
		else if($stmt->errorCode() === '23000'){
			echo 'WARNING: These contacts already exist...';
		}
		else{
			echo 'A MySQL error has occurred.';
		}

	});	
});