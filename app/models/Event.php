<?php
require_once(__DIR__.'/../lib/Model.php');

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

	public function getLocation(){
		return $this->location;
	}

	public function setLocation($location){
		$this->location = $location;
	}

	public function getStartTime(){
		return $this->startTime;
	}

	public function setStartTime($startTime){
		$this->startTime = $startTime;
	}

	public function getCreationTime(){
		return $this->creationTime;
	}

	public function setCreationTime($creationTime){
		$this->creationTime = $creationTime;
	}

	public function getUpdateTime(){
		return $this->updateTime;
	}

	public function setUpdateTime($updateTime){
		$this->updateTime = $updateTime;
	}

	public function getEndTime(){
		return $this->endTime;
	}

	public function setEndTime($endTime){
		$this->endTime = $endTime;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getCreatorUserID(){
		return $this->creatorUserID;
	}

	public function setCreatorUserID($creatorUserID){
		$this->creatorUserID = $creatorUserID;
	}

	public function getTimeZone(){
		return $this->timeZone;
	}

	public function setTimeZone($timeZone){
		$this->timeZone = $timeZone;
	}

	public function getRecurrence(){
		return $this->recurrence;
	}

	public function setRecurrence($recurrence){
		$this->recurrence = $recurrence;
	}

	public function getAttachments(){
		return $this->attachments;
	}

	public function setAttachments($attachments){
		$this->attachments = $attachments;
	}

	public function getEventID(){
		return $this->eventID;
	}

	public function setEventID($eventID){
		$this->eventID = $eventID;
	}
	
	public function create(){
		
	}
	
	public function update(){
		
	}
	
	public function delete(){
		
	}

	public function load(){
		
	}
}