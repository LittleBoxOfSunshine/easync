<?php

require_once(__DIR__.'/../lib/Model.php');
require __DIR__ . '/../../vendor/autoload.php';

class GoogleCalendar extends Model{
	const CLIENT_ID = '66468154963-kjvu0u6hohvv59l03d0gr8kcomi4pggd.apps.googleusercontent.com';
	const CLIENT_SECRET = 'oNwvkqFISBzSjxNeWtZcnrAo';
	const REDIRECT_URI = 'http://easync.com/api/v1.0/User/addGoogleCal';
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
				'calID' //NOTE: assign default of *
			)
		);
		
		$this->client = self::makeGoogleClient();
		// Refresh the token if it's expired.
		if($this->client->isAccessTokenExpired()) {
			$this->client->refreshToken($this->client->getRefreshToken());
			//save the token!
		}

		$this->calendarList = new Google_Service_Calendar($this->client);   


	}

	private static function makeGoogleClient(){
		$client = new Google_Client();
	    $client->setApplicationName("Easync");
	    $client->setClientId(self::CLIENT_ID);
	    $client->setClientSecret(self::CLIENT_SECRET);
	    $client->setRedirectUri(self::REDIRECT_URI);
	    $client->setAccessType('offline');   // Gets us our refreshtoken

	    $client->setScopes(array('https://www.googleapis.com/auth/calendar'));

	    if( isset($_SESSION['token']) ){
	    	$client->setAccessToken($_SESSION['token']);
	    }

	    return $client;
	}

	public static function requestAccess($app){
		$authUrl = self::makeGoogleClient()->createAuthUrl();
 		$app->redirect($authUrl);
	}
	
	public static function acceptAccess($app){
		$client = self::makeGoogleClient();
		if (!isset($_GET['code']))
			$client->authenticate($_GET['code']);  
		$_SESSION['token'] = $client->getAccessToken();
/*
		$stmt = Database::prepareAssoc("SELECT * FROM `User` WHERE token=:token");
		$stmt->execute();
		var_dump($stmt->fetch());
	*/
	}


	public function getEvents(){
		echo "getEvents";

		    $calendarList  = $this->calendarList->calendarList->listCalendarList();
				  while(true) {
				      foreach ($calendarList->getItems() as $calendarListEntry) {
				        echo $calendarListEntry->getSummary()."<br>\n";
				        // get events 
				        $events = $this->calendarList->events->listEvents($calendarListEntry->id);
				        foreach ($events->getItems() as $event) {
				            echo "-----".$event->getSummary()."<br>";
				        }
				      }
				      $pageToken = $calendarList->getNextPageToken();
				      if ($pageToken) {
				        $optParams = array('pageToken' => $pageToken);
				        $calendarList = $this->calendarList->calendarList->listCalendarList($optParams);
				      } else {
				        break;
				      }
				  }
	  	
	}
	
}



    