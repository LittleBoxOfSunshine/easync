'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:LoginCtrl
 * @description
 * # LoginCtrl
 * Controller of the easyncApp
 */

var GLOBAL_IP = "http://52.27.123.122/";

angular.module('easyncApp')
  .controller('LoginCtrl', function ($scope, $http, $cookies) {
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
    		url: GLOBAL_IP + 'api/v1.0/User/login',
    		method: 'POST',
    		data: login_json/*,
    		withCredentials: true,
    		headers : {
    			"Access-Control-Allow-Credentials" : "true",
    			"Access-Control-Allow-Origin": "*"
    		}*/
    	})
    	.then(function(response) {
    		console.log(response);
    		if (response.data === 'Login successful') {
    			$scope.email = '';
    			$scope.pass = '';
    		}


    		console.log(response.data);
    		console.log($cookies.getAll());
    	},function(error) {
    		console.log(error);
    		$scope.pass = '';
    	});
    };
});