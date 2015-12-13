<?php

$app->group('/api/v1.0/Meeting', function() use ($app, $AUTH_MIDDLEWARE) {
	
	/*
	 * Eric Smith
	 */

	//for giving list of meeting possibilities
	$app->post('/planMeeting', $AUTH_MIDDLEWARE(), function () use ($app){

		$meeting = json_decode($app->request()->getBody());

		//required for calendar diffing
		$emails = $meeting->emails;
		$length = $meeting->length;
		$dayEnd = $meeting->dayEnd;
		$dayStart = $meeting->dayStart;
		$allRequired = $meeting->allRequired;
		$startTime =  new DateTime($meeting->EventDetails->startTime);
		$endTime = new DateTime($meeting->EventDetails->endTime);
		
		//not required - passed as json encoded cookie
		$meetingDetails = [];
		$meetingDetails['name'] = $meeting->EventDetails->name;
		$meetingDetails['description'] = $meeting->EventDetails->description;
		$meetingDetails['creatorEmail'] = $meeting->EventDetails->creatorEmail;
		$meetingDetails['location'] = $meeting->EventDetails->location;
		$meetingDetails['attachments'] = $meeting->EventDetails->attachments;

		//set cookie for finalizeMeeting
		$_SESSION['meetingDetails'] = $meetingDetails;

		//compensate for timezone
		$startTime = $startTime->format('Y-m-d\TH:i:sP');
		$startTime = substr($startTime, 0, -6);
		$startTime = $startTime . "-06:00";

		$endTime = $endTime->format('Y-m-d\TH:i:sP');
		$endTime = substr($endTime, 0, -6);
		$endTime = $endTime . "-06:00";


		$allEvents = [];

		/*
		 * Foreach user with a valid account,
		 * merge their events - turn them into free times
		 * add to allEvents
		 */
		foreach($emails as $email){
			$stmt = Database::prepareAssoc("SELECT `userID` FROM `User` WHERE email=:email");
			$stmt->bindParam(':email', $email);
			$stmt->execute();
			$userID = $stmt->fetch();

			if($userID != false){
				$userID = $userID['userID'];
				
				$cal = new GoogleCalendar(array('userID' => $userID));
				$mergedEvents = $cal->merge_ranges( $cal->getEvents($startTime, $endTime) );
				$invertedEvents = $cal->invertEvents($mergedEvents);
				$minuteEvents = $cal->convertToMinutes( $invertedEvents, $startTime );

				$allEvents[$email] = $minuteEvents;
			} 

		}

		//convert json parameters to minutes
		//first rule of computer science: repeat yourself

		$start = explode(':', $dayStart);
		$minutes = $start[0] * 60;
		$minutes += $start[1];

		$dayStart = $minutes;

		$end = explode(':', $dayEnd);
		$minutes = $end[0] * 60;
		$minutes += $end[1];

		$dayEnd = $minutes;

		$length = explode(':', $length);
		$minutes = $length[0] * 60;
		$minutes += $length[1];

		$length = $minutes;

		$sT = substr($startTime, -14, 8);
		$sT = explode(':', $sT);
		$minutes = $sT[0] * 60;
		$minutes += $sT[1];
		$sT = $minutes;

		$eT = substr($endTime, -14, 8);
		$eT = explode(':', $eT);
		$minutes = $eT[0] * 60;
		$minutes += $eT[1];
		$eT = $minutes;

		//get the top 5 times to meet
		$tree = new CalIntervalDiff($allEvents, $sT, $eT, $dayStart, $dayEnd, $length);
		$meetingTimes = $tree->getTop(5);

		//make sure everyone can attend
		if($allRequired){
			if(count($emails) != count($meetingTimes[0]['people'])){
				echo "A meeting time is not possible for all members.";
				return;
			}
		}

		//store info (sessionMeetings) in a session 
		//sessionMeetings contains same info as finalMeetings with the same indexes
		//but its information is in a different format for the database
		$sessionMeetings = [];


		foreach($meetingTimes as $meet) {
			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['startTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d H:i:s');

			$meet['startTime'] = $newTime;

			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['endTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d H:i:s');

			$meet['endTime'] = $newTime;

			$sessionMeetings[] = $meet;
		}

		//cookie sent to finalizeMeetings
		$_SESSION['meetings'] = $sessionMeetings;

		//convert to non 0 indexed and change email to names
		$finalMeetings = [];

		foreach($meetingTimes as $meet) {
			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['startTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d\TH:i:sP');
			$newTime = substr($newTime, 0, -6);
			$newTime = date("D, M d g:i A", strtotime($newTime));
			
			$meet['startTime'] = $newTime;

			$start = new DateTime($startTime);

			$newTime = $start->add(new DateInterval('PT' . $meet['endTime'] . 'M'));
			$newTime = $newTime->format('Y-m-d\TH:i:sP');
			$newTime = substr($newTime, 0, -6);
			$newTime = date("D, M d g:i A", strtotime($newTime));

			$meet['endTime'] = $newTime;

			$newPeople = [];

			$stmt = Database::prepareAssoc("SELECT `name` FROM `User` WHERE email=:person");
			$stmt->bindParam(':person', $person);
			
			foreach($meet['people'] as $person){
				$stmt->execute();

				$newPeople[] = $stmt->fetch()['name'];
			}

			$meet['people'] = $newPeople;

			$finalMeetings[] = $meet;
		}

		echo json_encode( $finalMeetings );
		
	});
	
	/*
	 * Eric Smith
	 */

	//creates the meeting from the chosen index given in planMeeting
	//must run after planMeeting
	$app->post('/finalizeMeeting', $AUTH_MIDDLEWARE(), function () use ($app){

		if( isset($_SESSION['meetings']) ){

			//load meeting details from session using index (time range) given as input	
			$index = json_decode($app->request()->getBody());

			if($index === NULL)
				die("Index was not sent in json.");

			$index = $index->index;

			$meetingDetails = $_SESSION['meetingDetails'];
			$name = $meetingDetails['name'];
			$description = $meetingDetails['description'];
			$creatorEmail = $meetingDetails['creatorEmail'];
			$location = $meetingDetails['location'];
			$attachments = $meetingDetails['attachments'];

			$creationTime = date('Y-m-d H:i:s');

			//holds attendees and start/end times
			$meeting = $_SESSION['meetings'][$index];

			$creatorUserID = User::emailToUser($creatorEmail);
			
			//insertthe meeting into the db
			$stmt = Database::prepareAssoc("INSERT INTO `MeetingDetails` (location, startTime, creationTime, endTime, name, description, creatorUserID, attachments)
				VALUES (:location, :startTime, :creationTime, :endTime, :name, :description, :creatorUserID, :attachments);");
			$stmt->bindParam(':location', $location);	
			$stmt->bindParam(':startTime', $meeting['startTime']);
			$stmt->bindParam(':creationTime', $creationTime);
			$stmt->bindParam(':endTime', $meeting['endTime']);
			$stmt->bindParam(':name', $name);			
			$stmt->bindParam(':description', $description);
			$stmt->bindParam(':creatorUserID', $creatorUserID);
			$stmt->bindParam(':attachments', $attachments);
			$stmt->execute();


			$meetingID = Database::lastInsertId();

			$stmt = Database::prepareAssoc("SELECT `name` FROM `User` WHERE email=:email");
			$stmt->bindParam(':email', $creatorEmail);
			$stmt->execute();

			$creatorName = $stmt->fetch();			
			$creatorName = $creatorName['name'];

			//foreach email, add a row in the database containing the meetingID
			$stmt = Database::prepareAssoc("INSERT INTO `Meeting` (`email`, `meetingID`) VALUES(:email, :meetingID);");
			$stmt->bindParam(':email', $email);
			$stmt->bindParam(':meetingID', $meetingID);

			foreach($meeting['people'] as $email)
				$stmt->execute();

			User::sendConfEmails($meeting['people'], $meetingID);

			echo "Your meeting has been scheduled.";
			
		}

		else{
			die('No meetings cookie was set.. planMeeting was never called?');
		}

	});	

	$app->post('/rsvp', $AUTH_MIDDLEWARE(), function () use ($app){
		global $USER_ID;
		$token = $app->request->post('token');

		$stmt = Database::prepareAssoc("UPDATE Meeting SET rsvp = 'True' WHERE :token = token;");
		$stmt->bindParam(':token', $token);
		$stmt->execute();

		if($stmt->errorCode() === '00000'){
			echo 'Successfully Added to Meeting.';
		}
		else {
			echo 'mySQL error.';
		}

	});

});