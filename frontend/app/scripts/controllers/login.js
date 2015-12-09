'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the easyncApp
 */

angular.module('easyncApp')
  .controller('LoginCtrl', function ($scope, $http, $cookies, GlobalIPService, LoggedInService, $location) {
    //$scope.userLoggedIn = true;

    $scope.email = '';
    $scope.pass = '';

    $scope.login = function(email, pass) {
    	var login_data = {
    		'email': email, 
    		'password': pass
    	};

    	var login_json = JSON.stringify(login_data);

    	$http({
    		url: GlobalIPService.ip + 'api/v1.0/User/login',
    		method: 'POST',
    		data: login_json,
            withCredentials: true
    	})
    	.then(function(response) {
    		console.log(response);
    		if (response.data === 'Login successful') {
    			$scope.email = '';
    			$scope.pass = '';

                //set the cookie for being logged in
                LoggedInService.set_or_refresh_cookie();
                //redirect back to dashboard
                $location.path('');
    		}
    	},function(error) {
    		console.log(error);
    		$scope.pass = '';
    	});
    };
});