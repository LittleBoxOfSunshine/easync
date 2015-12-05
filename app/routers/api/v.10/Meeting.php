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
			! copy emails, prune those w/out googlecal acess
			! load any tokens, construct any API objects
			pull events
			diff by all attendees - look up group scheduling algorithm 
				if impossibr and required
					give failure message
				else if not required
					move on
			diff, rank by max #tendies
			give top options or failure if no optoins exist (store copy of results in session)
			
		*/

		$meetingID = $app->request->post('meetingID');

		$meeting = json_decode($app->request()->getBody());

		$emails = $meeting->emails;
		$length = $meeting->length;
		$dayEnd = $meeting->dayEnd;
		$dayStart = $meeting->dayStart;
		$allRequired = $meeting->allRequired;
		$startTime =  new DateTime($meeting->EventDetails->startTime);
		$endTime = new DateTime($meeting->EventDetails->endTime);
		$name = $meeting->EventDetails->name;
		$creatorUserID = $meeting->EventDetails->creatorUserID;

		//$startTime->setTimeZone(new DateTimeZone($meeting->EventDetails->timeZone));
		$startTime = $startTime->format('Y-m-d\TH:i:sP');
		//$endTime->setTimeZone(new DateTimeZone($meeting->EventDetails->timeZone));
		$endTime = $endTime->format('Y-m-d\TH:i:sP');

		$allEvents = [];

		foreach($emails as $email){
			$stmt = Database::prepareAssoc("SELECT `userID` FROM `User` WHERE email=:email");
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$userID = $stmt->fetch();

			if($userID == false)
				echo "ERROR: The user: " . $email . " does not have a userID\n";
			else{
				$userID = $userID['userID'];
				
				$cal = new GoogleCalendar(array('userID' => $userID));
				$allEvents[] = $cal->getEvents($startTime, $endTime);
			} 

		}

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
	
	$app->post('/rsvp', $AUTH_MIDDLEWARE(), function() use ($app){
		$rsvpToken = $app->request->post('rsvpToken');
		
		// modify attendie entry to acception
		
	});
});