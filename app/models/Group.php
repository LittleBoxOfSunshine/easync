<?php
require_once('../lib/Model.php');

class Group extends Model implements CRUD{
	protected $creationTime;
	protected $name;
	protected $description;
	protected $creatorUserID;
  	protected $logo;
  	protected $groupID;
  	protected $userIDs;

	public function __construct(array $args = array()){
		parent::__construct($args, array(
			array('groupID'),
			array('name','creatorID')
			));
	}

	public function getCreationTime(){
		return $this->creationTime;
	}

	public function setCreationTime($creationTime){
		$this->creationTime = $creationTime;
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

	public function getLogo(){
		return $this->logo;
	}

	public function setLogo($logo){
		$this->logo = $logo;
	}

	public function getGroupID(){
		return $this->groupID;
	}

	public function setGroupID($groupID){
		$this->groupID = $groupID;
	}

	public function getUserIDs(){
		return $this->userIDs;
	}

	public function setUserIDs(array $userIDs = array()){
		$this->userIDs = $userIDs;
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
