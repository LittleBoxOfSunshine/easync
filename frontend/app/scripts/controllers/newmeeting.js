'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the easyncApp
 */

angular.module('easyncApp')
  .controller('NewMeetingCtrl', function ($scope, $http) {

    $scope.attendees = [{'email': 'swilkinsonhunter@gmail.com'}];
    
    $scope.addemailattendee = function(email) {
    	$scope.attendees.push({'email': email});
    	$scope.attendeeemail = "";
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
