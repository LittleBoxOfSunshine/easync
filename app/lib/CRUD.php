<?php

// This interface is required to use the database object, distinguishes between actions 
// that involve just a memory object and those that involve the database
interface CRUD{
	
	// Uses instance properties to create a representation of the memory object in the database
	public function create();
	
	// Uses instance properties to update the database representation of the object to match its current state
	public function update();
	
	// Deletes the database representation of the memory object
	public function delete();
	
	// Loads data from the database into instance properties using some minimum subset of instance properties
	// The different combinations of properties that can be used is implementation specific. Multiple valid
	// combinations are allowed
	public function load();
}