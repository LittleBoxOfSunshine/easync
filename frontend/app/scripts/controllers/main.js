'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the easyncApp
 */


angular.module('easyncApp')
.controller('MainCtrl', function ($scope, $http, $cookies, LoggedInService, $location) {
    //check for cookies immediately

    $scope.check_cookies = function () {
    	if (!LoggedInService.loggedIn()) {
    		$location.path('login');
    	}
    };
});
