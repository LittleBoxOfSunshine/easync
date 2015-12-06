'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:HomeCtrl
 * @description
 * # HomeCtrl
 * Controller of the home page 
 */
angular.module('easyncApp')
.controller('HomeCtrl', function ($scope) {
    console.log($scope);
    this.awesomeThings2 = [
      'Things 2'
    ];
});
