'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:NewMeetingCtrl
 * @description
 * # NewMeetingCtrl
 * Controller of the easyncApp
 */

var GLOBAL_IP = "http://52.27.123.122/";

angular.module('easyncApp')
  .controller('NewMeetingCtrl', function ($scope, $http, $cookies) {

  	$scope.possibletimes_bool = false;

    $scope.attendees = [{'email': 'swilkinsonhunter@gmail.com'}];

    $scope.usercontacts = [];

    $scope.constraints = {
    	start_time : new Date(),
    	end_time : new Date(),
    	start_date : new Date(),
    	end_date : new Date(),
    	duration: 0,
    	required : false
    };
    
    $scope.addemailattendee = function(email) {
    	$scope.attendees.push({'email': email});
    	$scope.attendeeemail = "";
    };

    $scope.loadcontacts = function() {
    	$http.get(GLOBAL_IP + 'api/v1.0/User/getContacts', '').success(function (data) {
    		console.log(data);
    	}).error(function (error) {
    		console.log(error);
    	});
    	$scope.usercontacts.push({'name': 'jorge'}, {'name': 'sam'});
    };

   	//accepts the user object from contact list, removes it from contact list and adds it to attendee list
    $scope.addcontacttoattendees = function(user) {
    	$scope.attendees.push({'name': user.name});
    	$scope.usercontacts = $scope.usercontacts.filter(function (element) {
    		return user.name !== element.name;
    	});
    };

    //removes people from attendees list, adds them back to contacts list if needed
    $scope.removefromattendees = function(user) {
    	//if the user is a contact
    	if (user.name !== undefined) {
    		$scope.usercontacts.push(user);
    		$scope.attendees = $scope.attendees.filter(function (element) {
    			return element.name !== user.name;
    		});
    	} else if (user.email !== undefined) {
    		$scope.attendees = $scope.attendees.filter(function (element) {
    			return element.email !== user.email;
    		});
    	}
    };

    $scope.validateconstraintfields = function() {
    	if ($scope.constraints.start_date > $scope.constraints.end_date) {
    		return false;
    	} else if ($scope.constraints.start_time >= $scope.constraints.end_time) {
    		return false;
    	} else if ($scope.constraints.duration === 0) {
    		return false;
    	} else {
    		return true;
    	}
    };

}).filter('attendeesValue', function() { 
	return function(input) {
		return input[Object.keys(input)[0]];
	}; //used to get either the email or name of the attendees for the newmeeting page
	//need this because attendees object can either have 'email' or 'name' key depending
	//on how it was added to the list 
	//relevant links: https://docs.angularjs.org/api/ng/directive/ngRepeat
	//https://docs.angularjs.org/guide/filter
});
