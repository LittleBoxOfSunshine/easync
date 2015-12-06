/*global $:false */
'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:RegisterCtrl
 * @description
 * # RegisterCtrl
 * Controller of the easyncApp
 */

var GLOBAL_IP = "http://52.27.123.122/";

angular.module('easyncApp')
  .controller('RegisterCtrl', function ($scope, $http) {
    $scope.user = {
    	firstname : '',
    	lastname : '',
    	email : '',
    	pass : ''
    };

    this.errors = [];

    this.register = function(user) {
    	this.errors = [];
    	console.log(user);
    	for (var e in user) {
    		if (user[e] === '' || typeof(user[e]) === 'undefined') {
    			this.errors.push('new error');
    		}
    	}

    	if (this.errors.length !== 0) {
    		return;
      }

    	var payload = {
	      	'email' : user.email,
	      	'password' : user.pass,
	      	'firstname' : user.firstname,
	      	'lastname' : user.lastname
    	};

    	console.log(payload);

    	$http.post(GLOBAL_IP + 'api/v1.0/User/register', payload).success(function (data) {
	        window.alert('Register successful!');
          console.log(data);
      }).error(function (error) {
	      	console.log('Register failed ' + error);

        
      });
    };
  });

  
angular.module('easyncApp')
	.directive('pwcheck', function() {
		return {
			require: 'ngModel',
			link: function(scope, elm, attrs, ctrl) {
				var first_pass = '#' + attrs.pwcheck;
				elm.add(first_pass).on('keyup', function() {
					scope.$apply(function() {
						var v = elm.val() === $(first_pass).val();
						ctrl.$setValidity('pwcheck', v);
					});
				});
			}
		};
});


