<?php
require_once('../lib/Model.php');

class Group extends Model implements CRUD{
	protected $creationTime;
	protected $name;
	protected $description;
	protected $creatorUserID;
  protected $logo;
  protected $groupID;
  protected $userID;

	public function __construct(array $args = array()){
		parent::__construct(
			$args,
			array(
				array('groupID'),
				'groupID',
				'name',
        		'descripition',
				'creatorUserID',
				'creationtime',
        		'logo'
			));

	}
}
