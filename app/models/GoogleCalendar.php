<?php
/*
 * Eric Smith
 */
require_once(__DIR__.'/../lib/Model.php');
require __DIR__ . '/../../vendor/autoload.php';
class GoogleCalendar extends Model{
	const CLIENT_ID = '66468154963-kjvu0u6hohvv59l03d0gr8kcomi4pggd.apps.googleusercontent.com';
	const CLIENT_SECRET = 'oNwvkqFISBzSjxNeWtZcnrAo';
	const REDIRECT_URI = 'http://easync.jorgev.me/api/v1.0/User/addGoogleCal';
	const PLATFORM_ID = 'Google';

	protected $userID;
	protected $calID;
	protected $token;
	protected $client;
	protected $calendarList;

	public function __construct(array $args = array()){
	  
	    $args = array_merge(array(
	    	'calID' => self::PLATFORM_ID
	    ), $args);

	    parent::__construct($args,
			array(
				'userID',
				'calID'
			)
		);
	    
		$this->client = self::makeGoogleClient($this->userID);
		
		// Refresh the token if it's expired.
		if($this->client->isAccessTokenExpired()){
			$temp = json_decode($this->client->getAccessToken());

			if($temp == NULL)
				die("ERROR: user has not allowed easync to access their Google Calendar");

			$this->client->refreshToken( $this->client->REFRESH_TOKEN_DB );
			$token = $this->client->getAccessToken();

			//save the token
			$stmt = Database::prepareAssoc("UPDATE `CalendarTokens` SET token=:token WHERE userID=:userID AND platformID=:platformID");
			$stmt->bindParam(':token', $token);
			$stmt->bindParam(':platformID', $this->calID);
			$stmt->bindParam(':userID', $this->userID);
			$stmt->execute();
		}
		
		
		$this->calendarList = new Google_Service_Calendar($this->client);   

	}

	private static function makeGoogleClient($USER_ID){
		$client = new Google_Client();
	    $client->setApplicationName("Easync");
	    $client->setClientId(self::CLIENT_ID);
	    $client->setClientSecret(self::CLIENT_SECRET);
	    $client->setRedirectUri(self::REDIRECT_URI);
	    $client->setAccessType('offline');   //Gets us our refreshtoken

	    $client->setScopes(array('https://www.googleapis.com/auth/calendar'));

	    $platID = self::PLATFORM_ID;


	    $calToken = self::loadTokens($USER_ID);

	    if( $calToken !== false ){
	    	$client->setAccessToken($calToken['token']);
	    	$client->REFRESH_TOKEN_DB = $calToken['refreshToken'];
	    }

	    return $client;
	}

	private static function loadTokens($USER_ID){
		$platID = self::PLATFORM_ID;

	    $stmt = Database::prepareAssoc("SELECT `token`, `refreshToken` FROM `CalendarTokens` WHERE userID=:userID AND platformID=:platformID");
		$stmt->bindParam(':userID', $USER_ID);
		$stmt->bindParam(':platformID', $platID );
		$stmt->execute();
		$allTokens = $stmt->fetch();

		if( $allTokens !== false )
			return $allTokens;
		else
			return false;
	}

	public static function requestAccess($app, $USER_ID){
		//$app->response->headers->set('Content-Type', 'application/javascript');
		$authUrl = self::makeGoogleClient($USER_ID)->createAuthUrl();
 		$app->redirect($authUrl);
	}
	
	public static function acceptAccess($userID){
		$client = self::makeGoogleClient($userID);
		$client->authenticate($_GET['code']);  

		$token = $client->getAccessToken();

		$refreshToken = json_decode($token);
		$refreshToken = $refreshToken->refresh_token;

		$platID = self::PLATFORM_ID;

		$stmt = Database::prepareAssoc("INSERT INTO `CalendarTokens` (token, refreshToken, userID, platformID) VALUES (:token, :refreshToken, :userID, :platformID) ON DUPLICATE KEY UPDATE token=:token, refreshToken=:refreshToken");
		$stmt->bindParam(':token', $token);
		$stmt->bindParam(':refreshToken', $refreshToken);
		$stmt->bindParam(':userID', $userID);
		$stmt->bindParam(':platformID', $platID);
	    $stmt->execute();
	}

	public function createEvent($email, $startTime, $endTime, $meetingDetails){
		$start = new DateTime($startTime);
		$start = $start->format('Y-m-d\TH:i:sP');
		$start = substr($start, 0, -6);
		$start = $start . "-06:00";

		$end = new DateTime($endTime);
		$end = $end->format('Y-m-d\TH:i:sP');
		$end = substr($end, 0, -6);
		$end = $end . "-06:00";

		$event = new Google_Service_Calendar_Event(array(
		  'summary' => $meetingDetails['name'],
		  'location' => $meetingDetails['location'],
		  'description' => $meetingDetails['description'],
		  'start' => array(
		    'dateTime' => $start,
		    'timeZone' => 'America/Chicago',
		  ),
		  'end' => array(
		    'dateTime' => $end,
		    'timeZone' => 'America/Chicago',
		  )
		));
	

		$this->calendarList->events->insert($email, $event);
	}

	//get events from Google Calendar within time restrictions
	public function getEvents($startTime, $endTime){
		$calendarList = $this->calendarList->calendarList->listCalendarList();
		
		$finalizedEvents = [];

		$firstEvent = [];
		$firstEvent['startTime'] = $startTime;
		$firstEvent['endTime'] = $startTime;

		$finalizedEvents[] = $firstEvent;

		while(true){
		    foreach($calendarList->getItems() as $calendarListEntry){
			    //get all events
			    $events = $this->calendarList->events->listEvents($calendarListEntry->id, array('singleEvents' => 'true', 'timeMin' => $startTime, 'timeMax' => $endTime) );
			    foreach($events->getItems() as $event){
			    	$start = $event->getStart()->dateTime;
			    	$end = $event->getEnd()->dateTime;


			    	if($start !== NULL || $end !== NULL ){
				    	$insertEvent = [];
				    	$insertEvent['startTime'] = $start;
				    	$insertEvent['endTime'] = $end;

				    	$finalizedEvents[] = $insertEvent;
				    }

			    }
		    }

		    $pageToken = $calendarList->getNextPageToken();
		    if($pageToken) {
		        $optParams = array('pageToken' => $pageToken);
		        $calendarList = $this->calendarList->calendarList->listEvents($optParams);
		    } 
		    else{
		    	break;
		    }
		}

		$lastEvent = [];
		$lastEvent['startTime'] = $endTime;
		$lastEvent['endTime'] = $endTime;

		$finalizedEvents[] = $lastEvent;

		return $finalizedEvents;
	  	
	}

	//change merged events to free times
	public function invertEvents($merged){
		$inverted = [];

		for($i=0; $i<count($merged)-1; $i++){
			$curEvent = [];
			$curEvent['startTime'] = $merged[$i][1];
			$curEvent['endTime'] = $merged[$i+1][0];

			$inverted[] = $curEvent;
		}

		return $inverted;
	}

	//convert events to minutes based off the startTime
	//startTime = 0, one hour later = 60
	public function convertToMinutes($events, $startTime){
		$eventMinutes = [];
		$start = new DateTime($startTime);
		
		foreach($events as $event){
			$sinceStart = $start->diff( new DateTime($event['startTime']) );

			$minutes = $sinceStart->days * 24 * 60;
			$minutes += $sinceStart->h * 60;
			$minutes += $sinceStart->i;

			$event['startTime'] = $minutes;

			$sinceStart = $start->diff( new DateTime($event['endTime']) );

			$minutes = $sinceStart->days * 24 * 60;
			$minutes += $sinceStart->h * 60;
			$minutes += $sinceStart->i;

			$event['endTime'] = $minutes;

			$eventMinutes[] = $event;
		}

		return $eventMinutes;
	}

	public static function intcmp($a,$b) {
		if(strtotime($a) == strtotime($b)) return 0;
	    if(strtotime($a) > strtotime($b))return 1;
	    if(strtotime($a) < strtotime($b))return -1;
	}

	public static function compare($a, $b){
		$val = self::intcmp($a['startTime'], $b['startTime']);
		if($val == 0)
			$val = self::intcmp($a['endTime'], $b['endTime']);
		return $val;
	}


	//combine overlapping events
	public static function merge_ranges($meetings){
		//sort by start times, break ties with end times
		usort($meetings, 'self::compare');
		
		//meetings only go in mergedMeetings when we're sure they can't be merged further
		$mergedMeetings = [];

		$previousMeetingStart = $meetings[0]['startTime'];
		$previousMeetingEnd = $meetings[0]['endTime'];

		foreach( array_slice($meetings, 1) as $event => $time){
			$currentMeetingStart = $time['startTime'];
			$currentMeetingEnd = $time['endTime'];

			//if the previous meeting can be merged with the current one
			if( abs(strtotime($currentMeetingStart) - strtotime($previousMeetingEnd)) <= 60 ){
	        	$previousMeetingEnd = max($currentMeetingEnd, $previousMeetingEnd);
			}
			else{
				$mergedMeetings []= array($previousMeetingStart, $previousMeetingEnd);
				$previousMeetingStart = $currentMeetingStart;
				$previousMeetingEnd = $currentMeetingEnd;
			}
		}	

		$mergedMeetings []= array($previousMeetingStart, $previousMeetingEnd);

		return $mergedMeetings;
	}

}



    