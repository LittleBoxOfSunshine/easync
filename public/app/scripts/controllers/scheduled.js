'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:ScheduledCtrl
 * @description
 * # ScheduledCtrl
 * Controller of the home page 
 */
angular.module('easyncApp')
.controller('ScheduledCtrl', function ($scope, $http) {
    console.log($scope);
    $http.get("http://52.27.123.122/api/v1.0/User/getMeetings")
    	.then(function (response) {
    		console.log(response.data.meeting); //figure out what to put here eventually

    	});
});
