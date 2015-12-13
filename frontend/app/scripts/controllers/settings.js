'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:SettingsCtrl
 * @description
 * # SettingsCtrl
 * Controller of the easyncApp
 */


angular.module('easyncApp')
.controller('SettingsCtrl', function ($scope, $http, GlobalIPService, $location, $window) {
	$scope.contacts = [];
	$scope.calConnected = false;
	$scope.addContact = false;
	$scope.newContactError = false;
	$scope.name ='';
	 $scope.loadUser = function() {
    	$http({
    		method:'GET',
    		url: GlobalIPService.ip + 'api/v1.0/User/getUserDetails',
  			withCredentials: true
    	}).then(function (response) {
    			$scope.name = response.data.name;

    	}, function (error) {
    		console.log(error);
    	});
    };

	$scope.loadContacts = function() {
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

	$scope.checkCalendar = function() {
		$http({
			url: GlobalIPService.ip + 'api/v1.0/User/hasConnectedGoogleCal',
			method: 'GET',
			withCredentials: true
		}).then(function (response) {
			if (response.data === true) {
				$scope.calConnected = true;
			}
		}, function (error) {
			console.log("error fetching whether cal exists", error);
		});
	};

		// $window.location.href(GlobalIPService.ip + 'api/v1.0/User/addGoogleCal');
	// };

	$scope.toggleAddContact = function () {
		if ($scope.addContact) {
			$scope.addContact = false;
		} else {
			$scope.addContact = true;
		}
	};

	$scope.handleNewContact = function ($event) {
		$scope.newContactError = false;
		if ($event.keyCode === 13) {
			var newContactEmail = $scope.newContact;
			$http({
				method: 'POST',
				url: GlobalIPService.ip + 'api/v1.0/User/addContactsCheckEmail',
				data: JSON.stringify({'email': newContactEmail}),
				withCredentials: true
			}).then(function (response) {
				if (response.data.name !== undefined) {
					$scope.contacts.push({'name': response.data.name, 'email': response.data.email});
					$scope.newContact = '';
				} else if (response.data === 'no user found for email') {
					$scope.newContactError = true;
				}
			}, function (error) {
				console.log(error);
			}); 
		}
	};

	$scope.removeContact = function (user) {
		$http({
			method: 'GET',
			url: GlobalIPService.ip + 'api/v1.0/User/removeContact',
			withCredentials: true,
			params: {'email' : user.email}
		}).then(function(response) {
			console.log(response.data);
			if (response.data === 'Contact Removed') {
				$scope.contacts = $scope.contacts.filter(function (element) {
					return element.email !== user.email;
				});
			}
		}, function (error) {
			console.log(error);
		});
};

});
