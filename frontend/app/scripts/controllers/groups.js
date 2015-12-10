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
    
    $scope.loadGroups = function() {
    	$http({
    		method:'GET',
    		url: GlobalIPService.ip + 'api/v1.0/Group/getGroups',
  			withCredentials: true
    	}).then(function (response) {
    		console.log(response.data);
    	}, function (error) {
    		console.log(error);
    	});
    };

});
