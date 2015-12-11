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
  	$scope.selectedgroup = [];
  	$scope.groupname = '';
  	$scope.newRow = false;
    
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

    $scope.changeGoup = function (group_members, group_name) {
    	$scope.selectedgroup = group_members;
    	$scope.groupname = group_name;
    };

    $scope.addrow = function() {
    	$scope.newRow = true;
    };

    $scope.saverow = function(newName, newEmail) {
    	//save new contact to contact list
    	var data = {'name': newName, 'email': newEmail, 'groupName': $scope.groupname};
    	var json_data = JSON.stringify(data);

    	var handleSuccessFindingUser = function(response) {
    		if (response.data) {
	    		$http({
	    			method: 'POST',
	    			url: GlobalIPService.ip + 'api/v1.0/Group/addContactToGroup',
	    			data: json_data,
	    			withCredentials: true
	    		}).then(function (response) {
	    			if (response.data === 'Person Added to Group') {
	    				$scope.newRow = false;
	    				$scope.selectedgroup.push({'name': newName, 'email': newEmail});
	    				$scope.groups[$scope.groupname] = $scope.selectedgroup;
	    			}
	    		}, function (error) {
	    			console.log(error);
	    		});
	    	} else {
	    		console.log("user does not exist", response.data);
	    	}
    	};

    	if (newName !== '' && newEmail !== '') {
    		var exists_data = {email : newEmail};

    		$http({
    			method: 'GET',
    			url: GlobalIPService.ip + 'api/v1.0/User/exists',
    			params: exists_data,
    			withCredentials: true
    		}).then(handleSuccessFindingUser, function (error) {
    			console.log(error);
    		});
    	}


    	
    };

});
