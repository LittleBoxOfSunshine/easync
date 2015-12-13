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
  	$scope.contacts = [];
    
    //load groups from DB and display them

    $scope.changeColor = function(person, bool) {
        if(bool === true) {
            $scope.personColour = {color: '#FF5252'};
        } else if (bool === false) {
            $scope.personColour = {color: '#F9F9F9'}; //or, whatever the original color is
        }
    };

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

    $scope.saverow = function(newName, newEmail, newContact) {
    	//save new contact to contact list
    	newContact = JSON.parse(newContact);

    	var handleSuccessFindingUser = function(json_data) {
    		$http({
    			method: 'POST',
    			url: GlobalIPService.ip + 'api/v1.0/Group/addContactToGroup',
    			data: json_data,
    			withCredentials: true
    		}).then(function (response) {
    			if (response.data === 'Person Added to Group') {
    				$scope.newRow = false;
    				if (newContact) {
    					$scope.selectedgroup.push({'name': newContact.name, 'email': newContact.email});
    				} else {
    					$scope.selectedgroup.push({'name': newName, 'email': newEmail});
    				}
    				
    				$scope.groups[$scope.groupname] = $scope.selectedgroup;
    			}
    		}, function (error) {
    			console.log(error);
    		});
    	};

    	if (newContact !== undefined) {
    		var data = {'name': newContact.name, 'email': newContact.email, 'groupName': $scope.groupname};
    		var json_data = JSON.stringify(data);
    		handleSuccessFindingUser(json_data);
    	} else if (newName !== '' && newEmail !== '') {
    		var exists_data = {email : newEmail};

    		$http({
    			method: 'GET',
    			url: GlobalIPService.ip + 'api/v1.0/User/exists',
    			params: exists_data,
    			withCredentials: true
    		}).then(function (response) {
    			if (response.data) {
    				var data = {'name': newName, 'email': newEmail, 'groupName': $scope.groupname};
    				var json_data = JSON.stringify(data);
    				handleSuccessFindingUser(json_data);
    			} else {
    				console.log("user does not exist", response.data);
    			}
    		}, function (error) {
    			console.log(error);
    		});
    	}
    };

    $scope.loadcontacts = function () {
    	$http({
			url: GlobalIPService.ip + 'api/v1.0/User/getContactsInfo',
			method: 'GET',
			withCredentials: true
		}).then(function (response) {
			for (var i = 0; i < response.data.length; i++) {
                if (response.data[i].name !== undefined && response.data[i].email !== undefined) {
                    $scope.contacts.push({'name': response.data[i].name, 'email' : response.data[i].email});
                }
            }
		}, function (error) {
			console.log(error);
		});
    };
});
