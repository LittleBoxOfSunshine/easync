<?php
require_once('../lib/Model.php');

class Event extends Model implements CRUD{
	protected $location;
	protected $startTime;     
	protected $creationTime;  
	protected $updateTime;    
	protected $endTime;       
	protected $name;          
	protected $description;   
	protected $creatorUserID; 
	protected $timeZone;      
	protected $recurrence;    
	protected $attachments;   
	protected $eventID;  

	public function __construct(array $args = array()){
		parent::__construct(
			$args,
			array(
				'eventID',
				'userID',
				'name',
				'creatorUserID',
				'timeZone',
				'startTime',
				'creationtime',
				'endTime'
			));
			
	}
}