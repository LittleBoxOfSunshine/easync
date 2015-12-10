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
				get the free times of each person (sort.php)
					(not necessary to remember event names)
				find the max overpallping of $length

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


		$startTime = $startTime->format('Y-m-d\TH:i:sP');
		$startTime = substr($startTime, 0, -6);
		$startTime = $startTime . "-06:00";

		$endTime = $endTime->format('Y-m-d\TH:i:sP');
		$endTime = substr($endTime, 0, -6);
		$endTime = $endTime . "-06:00";

		$allEvents = [];

		/*
			make sure to compensate for startTime and endtime when 
			inverting takenTime to freeTime
		*/

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
				//$allEvents[] = $cal->getEvents($startTime, $endTime);
				$mergedEvents = $cal->merge_ranges( $cal->getEvents($startTime, $endTime) );
				$invertedEvents = $cal->invertEvents($mergedEvents);
				$minuteEvents = $cal->convertToMinutes( $invertedEvents, $startTime );

				$allEvents[] = $minuteEvents;
			} 

		}
		echo "<pre>";
		var_dump($allEvents);
		echo "</pre>";
	/*
		$output= array(
			array('startTime' => , 'endTime' => 840),
			array('startTime' => 9910, 'endTime' => 3660)
		);
	*/
	});

	//if exists, update rather than insert
	$app->post('/finalMeeting', $AUTH_MIDDLEWARE(), function () use ($app){
		
		//input: index of time option chosen
		$input = 4;

		$output = array(

		);

		/*
		
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