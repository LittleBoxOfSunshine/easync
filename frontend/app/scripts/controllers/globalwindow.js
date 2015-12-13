'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:GlobalWindowCtrl
 * @description
 * # GlobalWindowCtrl
 * Controller of the home page 
 */
angular.module('easyncApp')
.controller('GlobalWindowCtrl', function ($scope,LoggedInService, $location) {
    
    $scope.userLoggedIn = LoggedInService.loggedIn;

    $scope.logOut = function() {
    	LoggedInService.logout();
    };
});
