'use strict';

/**
 * @ngdoc function
 * @name easyncApp.controller:NewMeetingCtrl
 * @description
 * # NewMeetingCtrl
 * Controller of the easyncApp
 */



angular.module('easyncApp')
  .controller('NewMeetingCtrl', function ($scope, $http, $cookies, LoggedInService, GlobalIPService, $location) {
    $scope.possible_times = [];

  	$scope.possibletimes_bool = false;

    $scope.attendees = {'emails' : [], 'users' : [], 'groups' : []};

    $scope.usercontacts = [];

    $scope.groups = [];

    $scope.constraints = {
    	start_time : new Date(),
    	end_time : new Date(),
    	start_date : new Date(),
    	end_date : new Date(),
    	duration: 0,
    	required : false,
        title: '',
        location: '',
        description: '',
        attachment: ''
    };
    
    $scope.addemailattendee = function(email) {
    	$scope.attendees.emails.push({'email': email});
    	$scope.attendeeemail = ""; 
    };

    $scope.loadcontacts = function() {
        if (!LoggedInService.loggedIn) {
            $location.path('login');
        }

    	$http({
            url: GlobalIPService.ip + 'api/v1.0/User/getContactsInfo',
            method: 'GET',
            withCredentials : true
        }).then(function (response) {
            for (var i = 0; i < response.data.length; i++) {
                if (response.data[i].name !== undefined && response.data[i].email !== undefined) {
                    $scope.usercontacts.push({'name': response.data[i].name, 'email' : response.data[i].email});
                }
            }
    	}, function (error) {
    		console.log(error);
    	});
    };

   	//accepts the user object from contact list, removes it from contact list and adds it to attendee list
    $scope.addcontacttoattendees = function(user) {
    	$scope.attendees.users.push(user);
    	$scope.usercontacts = $scope.usercontacts.filter(function (element) {
    		return user.name !== element.name;
    	});
    };

    //removes people from attendees list, adds them back to contacts list if needed
    $scope.removefromattendees = function(user) {
        //if the user is a group
    	if (user.groupname !== undefined) {
            $scope.groups.push(user);
            $scope.attendees.groups = $scope.attendees.groups.filter(function (element) {
                return element.groupname !== user.groupname;
            });
        }
    	if (user.name !== undefined) { //if the user is a contact
    		$scope.usercontacts.push(user);
    		$scope.attendees.users = $scope.attendees.users.filter(function (element) {
    			return element.name !== user.name;
    		});
    	} else if (user.email !== undefined) {
    		$scope.attendees.emails = $scope.attendees.emails.filter(function (element) {
    			return element.email !== user.email;
    		});
    	}
    };

    $scope.validateconstraintfields = function() {
    	if ($scope.constraints.start_date > $scope.constraints.end_date) {
    		return false;
    	} else if ($scope.constraints.start_time >= $scope.constraints.end_time) {
    		return false;
    	} else if ($scope.constraints.duration === 0) {
    		return false;
    	} else if ($scope.constraints.title === '' || $scope.constraints.title === 'Meeting Name'){
            return false;
        } else {
    		return true;
    	}
    };

    $scope.loadgroups = function() {
        $http({
            url: GlobalIPService.ip + 'api/v1.0/Group/getGroupNames',
            method: 'GET',
            withCredentials: true
        }).then(function (response) {
            response.data.forEach(function(element, index, array) {
                $scope.groups.push({'groupname': element.name});
            });
        }, function (error) {
            console.log(error);
        });
    };

    $scope.addgrouptoattendees = function(group) {
        $scope.attendees.groups.push(group);
        $scope.groups = $scope.groups.filter(function (element) {
            return group.groupname !== element.groupname;
        });
    };

    $scope.findmeetingtimes = function(constraints, attendees) {
        var request_obj = {
            emails : [],
            EventDetails: {}
        };

        var handleDuration = function (time_minutes) {
            var sec_num = parseInt(time_minutes*60, 10);

            var hours = Math.floor(sec_num/3600);
            var minutes = Math.floor((sec_num - (hours*3600))/60);
            var seconds = Math.floor(sec_num - (hours*3600) - (minutes*60));

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
            var time    = hours+':'+minutes+':'+seconds;
            return time;
        };

        var handleTimeOfDay = function (date_obj) {
            var hours = date_obj.getHours();
            var minutes = date_obj.getMinutes();
            var seconds = date_obj.getSeconds();

            if (hours   < 10) {hours   = "0"+hours;}
            if (minutes < 10) {minutes = "0"+minutes;}
            if (seconds < 10) {seconds = "0"+seconds;}
            var time    = hours+':'+minutes+':'+seconds;
            return time;
        };

        var handleDate = function (date_obj, beginning_of_day) {
            var year = date_obj.getFullYear();
            var date = date_obj.getDate();
            var month = date_obj.getMonth() + 1; //returns 0-11

            if (year   < 10) {year   = "0"+year;}
            if (month < 10) {month = "0"+month;}
            if (date < 10) {date = "0"+date;}
            var final_date    = year+'-'+month+'-'+date;
            if (beginning_of_day) {
                return final_date + ' 00:00:00';
            } else {
                return final_date + ' 23:59:59';
            }

        };

        //set allrequired flag
        request_obj.allRequired = constraints.required;

        //set start and end times
        request_obj.dayStart = handleTimeOfDay(constraints.start_time);
        request_obj.dayEnd = handleTimeOfDay(constraints.end_time);

        //set start and end dates
        request_obj.EventDetails.startTime = handleDate(constraints.start_date, true);
        request_obj.EventDetails.endTime = handleDate(constraints.end_date, false);

        //set creator email cookie
        request_obj.EventDetails.creatorEmail = $cookies.get('easync_email');

        //set title, description, location
        request_obj.EventDetails.name = constraints.title;
        request_obj.EventDetails.description = constraints.description;
        request_obj.EventDetails.location = constraints.location;
        request_obj.EventDetails.attachments = constraints.attachment;

        //set duration key
        var time_string = handleDuration(constraints.duration);
        request_obj.length = time_string;
        //add the attendees to the email array
        attendees.emails.forEach(function(element, index, array) { //for emails
            request_obj.emails.push(element.email);
        });
        attendees.users.forEach(function(element, index, array) { //for users
            request_obj.emails.push(element.email);
        });


        var scheduleMeeting = function(response) {
            response.data.forEach(function (element, index, array) {
                request_obj.emails.push(element.email);
            });

            //add creator email to emails array
            request_obj.emails.push($cookies.get('easync_email'));

            //remove duplicate emails
            var before_trim = request_obj.emails.length;
            request_obj.emails = request_obj.emails.filter(function (value, index, element) {
                return element.indexOf(value) === index;
            });
            console.log('emails trimmed from ' + before_trim + ' to ' + request_obj.emails.length + ' email addresses');

            console.log(request_obj);

            $http({
                method: 'POST',
                url: GlobalIPService.ip + 'api/v1.0/Meeting/planMeeting',
                withCredentials: true,
                data: JSON.stringify(request_obj)
            }).then(function (response) {
                console.log(response.data);
                $scope.possible_times = response.data;
                $scope.possibletimes_bool = true;
            }, function (error) {
                console.log(error);
            });
        };

        //for groups
        var groupnames = attendees.groups.map(function (val) { return val.groupname; });
        $http({
            method: 'POST',
            url: GlobalIPService.ip + 'api/v1.0/Group/getGroupContents',
            withCredentials: true,
            data : JSON.stringify(groupnames)
        }).then(scheduleMeeting, function(error) {
            console.log(error);
        });
    };

    $scope.finalizeMeeting = function(meeting) {
        var index = $scope.possible_times.indexOf(meeting);
        var data = {'index' : index};
        console.log($cookies.getAll());

        $http({
            url: GlobalIPService.ip + 'api/v1.0/Meeting/finalizeMeeting',
            method: 'POST',
            withCredentials: true,
            data: JSON.stringify(data)
        }, function (response) {
            console.log(response.data);
        }, function (error) {
            console.log(error);
        });
    };

}).filter('attendeesValue', function() { 
	return function(input) {
        if (input.groupname !== undefined) {
		  return input.groupname;
        } else if (input.name !== undefined) {
          return input.name;
        } else {
          return input.email;
        }
	}; //used to get either the email or name of the attendees for the newmeeting page
	//need this because attendees object can either have 'email' or 'name' key depending
	//on how it was added to the list 
	//relevant links: https://docs.angularjs.org/api/ng/directive/ngRepeat
	//https://docs.angularjs.org/guide/filter
});
