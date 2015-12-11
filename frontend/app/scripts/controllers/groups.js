'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:GroupsCtrl
 * @description
 * # GroupsCtrl
 * Controller of the easyncApp groups page.
 */

angular.module('easyncApp')
  .controller('GroupsCtrl', function ($scope, $http, GlobalIPService) {

  	$scope.groups = {};
    
    //load groups from DB and display them
    $scope.loadGroups = function() {
    	$http({
    		method:'GET',
    		url: GlobalIPService.ip + 'api/v1.0/Group/getGroups',
  			withCredentials: true
    	}).then(function (response) {
    		for (var i in response.data) {
    			var g_name = response.data[i].groupName;
    			if (!$scope.groups[g_name]) {
    				$scope.groups[g_name] = [];
    			}
    			$scope.groups[g_name].push({'name': response.data[i].name, 'email': response.data[i].email});
    		}
    	}, function (error) {
    		console.log(error);
    	});
    };

});
