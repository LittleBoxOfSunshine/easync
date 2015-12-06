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
    console.log($scope);
    console.log($http);

    $scope.attendees = [{'email': 'swilkinsonhunter@gmail.com'}];
    
    $scope.addemailattendee = function(email) {
    	$scope.attendees.push({'email': email});
    	$scope.attendeeemail = "";
    };

});
