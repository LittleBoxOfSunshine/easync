/*global $:false */
'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:RegisterCtrl
 * @description
 * # RegisterCtrl
 * Controller of the easyncApp
 */

angular.module('easyncApp')
  .controller('RegisterCtrl', function ($scope, $http, GlobalIPService, LoggedInService, $location) {
    $scope.user = {
    	firstname : '',
    	lastname : '',
    	email : '',
    	pass : '',
      passtwo : ''
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

    	var json_payload = JSON.stringify(payload);
      console.log(json_payload);

    	$http.post(GlobalIPService.ip + 'api/v1.0/User/register', json_payload).success(function (data) {
          console.log(data);
          if (data === "Account Created.") {

            var login_data = {
              'email': user.email, 
              'password': user.pass
            };

            var login_json = JSON.stringify(login_data);

            $scope.user = {
              firstname : '',
              lastname : '',
              email : '',
              pass : '',
              passtwo : ''
            };

            $http({
              url: GlobalIPService.ip + 'api/v1.0/User/login',
              method: 'POST',
              data: login_json,
              withCredentials: true
            })
            .then(function(response) {
              console.log(response.data);
              if (response.data === 'Login successful') {
                      //set the cookie for being logged in
                      LoggedInService.set_or_refresh_cookie(user.email);
                      //redirect back to dashboard
                      window.location = GlobalIPService.ip + 'api/v1.0/User/addGoogleCal';
              }
            },function(error) {
              console.log(error);
            });

          }
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


