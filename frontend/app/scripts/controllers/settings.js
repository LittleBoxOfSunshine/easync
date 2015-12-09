'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:SettingsCtrl
 * @description
 * # SettingsCtrl
 * Controller of the easyncApp
 */


angular.module('easyncApp')
.controller('SettingsCtrl', function ($scope, $http, GlobalIPService) {
	$scope.contacts = [];
	$scope.calConnected = false;

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
			console.log(response.data);
			if (response.data === true) {
				$scope.calConnected = true;
			}
		}, function (error) {
			console.log("error fetching whether cal exists", error);
		});
	};

	$scope.connectCal = function() {
		$http({
			url: GlobalIPService.ip + 'api/v1.0/User/addGoogleCal',
			method: 'GET',
			withCredentials: true
		}).then(function (response) {
			console.log(response.data);
		}, function (error) {
			console.log("error connecting google cal", error);
		});
	};
});