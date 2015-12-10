'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:ScheduledCtrl
 * @description
 * # ScheduledCtrl
 * Controller of the home page 
 */
angular.module('easyncApp')
.controller('ScheduledCtrl', function ($scope, $http, GlobalIPService) {
    
    $scope.loadMeetings = function () {
    	$http.get(GlobalIPService.ip + "api/v1.0/User/getMeetings")
    	.then(function (response) {
    		console.log(response.data); //figure out what to put here eventually
    	}, function (error) {
    		console.log(error);
    	});
    };
    
});
