"use strict";angular.module("easyncApp",["ngAnimate","ngCookies","ngResource","ngRoute","ngSanitize","ngTouch","ui.calendar"]).config(["$routeProvider",function(a){a.when("/",{templateUrl:"views/main.html",controller:"MainCtrl",controllerAs:"main"}).when("/register",{templateUrl:"views/register.html",controller:"RegisterCtrl",controllerAs:"register"}).when("/login",{templateUrl:"views/login.html",controller:"LoginCtrl",controllerAs:"login"}).when("/calendar",{templateUrl:"views/calendar.html",controller:"CalendarCtrl",controllerAs:"calendar"}).when("/scheduled",{templateUrl:"views/scheduled.html",controller:"ScheduledCtrl",controllerAs:"scheduled"}).when("/newmeeting",{templateUrl:"views/newmeeting.html",controller:"NewMeetingCtrl",controllerAs:"newmeeting"}).when("/groups",{templateUrl:"views/groups.html",controller:"GroupsCtrl",controllerAs:"groups"}).when("/about",{templateUrl:"views/about.html",controller:"AboutCtrl",controllerAs:"about"}).when("/settings",{templateUrl:"views/settings.html",controller:"SettingsCtrl",controllerAs:"settings"}).when("/gbwindow",{templateUrl:"views/main.html",controller:"GlobalWindowCtrl",controllerAs:"windowcontrol"}).when("/techoverview",{templateUrl:"views/techoverview.html",controller:"TechOverviewCtrl",controllerAs:"techoverview"}).when("/devprocess",{templateUrl:"views/devprocess.html",controller:"DevProcessCtrl",controllerAs:"devprocess"}).otherwise({redirectTo:"/"})}]).factory("LoggedInService",["$cookies","$location",function(a,b){return{loggedIn:function(){return a.get("easync_logged")?!0:!1},set_or_refresh_cookie:function(){var b=new Date((new Date).valueOf()+18e5);return a.put("easync_logged","true",{expires:b}),!0},logout:function(){return this.loggedIn()?(a.remove("easync_logged"),a.remove("slim_session")):console.log("tried to log out, but wasn't logged in"),b.path("login"),!0}}}]).factory("GlobalIPService",function(){return{ip:"http://localhost:6969/"}}).config(["$httpProvider",function(a){a.defaults.headers.common={},a.defaults.headers.post={},a.defaults.headers.put={},a.defaults.headers.patch={}}]),angular.module("easyncApp").controller("MainCtrl",["$scope","$http","$cookies","LoggedInService","$location",function(a,b,c,d,e){a.check_cookies=function(){d.loggedIn()||e.path("login")}}]),angular.module("easyncApp").controller("RegisterCtrl",["$scope","$http","GlobalIPService","LoggedInService","$location",function(a,b,c,d,e){a.user={firstname:"",lastname:"",email:"",pass:"",passtwo:""},this.errors=[],this.register=function(f){this.errors=[],console.log(f);for(var g in f)(""===f[g]||"undefined"==typeof f[g])&&this.errors.push("new error");if(0===this.errors.length){var h={email:f.email,password:f.pass,firstname:f.firstname,lastname:f.lastname},i=JSON.stringify(h);console.log(i),b.post(c.ip+"api/v1.0/User/register",i).success(function(b){console.log(b),"Account Created."===b&&(a.user={firstname:"",lastname:"",email:"",pass:"",passtwo:""},d.set_or_refresh_cookie(),e.path(""))}).error(function(a){console.log("Register failed "+a)})}}}]),angular.module("easyncApp").directive("pwcheck",function(){return{require:"ngModel",link:function(a,b,c,d){var e="#"+c.pwcheck;b.add(e).on("keyup",function(){a.$apply(function(){var a=b.val()===$(e).val();d.$setValidity("pwcheck",a)})})}}}),angular.module("easyncApp").controller("LoginCtrl",["$scope","$http","$cookies","GlobalIPService","LoggedInService","$location",function(a,b,c,d,e,f){a.userLoggedIn=!1,a.email="",a.pass="",a.login=function(c,g){var h={email:c,password:g},i=JSON.stringify(h);b({url:d.ip+"api/v1.0/User/login",method:"POST",data:i,withCredentials:!0}).then(function(b){"Login successful"===b.data&&(a.email="",a.pass="",e.set_or_refresh_cookie(),f.path(""))},function(b){console.log(b),a.pass=""})},a.check_cookies=function(){e.loggedIn()&&(console.log("redirecting to dashboard"),f.path(""))}}]),angular.module("easyncApp").controller("NewMeetingCtrl",["$scope","$http","$cookies","LoggedInService","GlobalIPService","$location",function(a,b,c,d,e,f){a.possibletimes_bool=!1,a.attendees=[{email:"swilkinsonhunter@gmail.com"}],a.usercontacts=[],a.groups=[],a.constraints={start_time:new Date,end_time:new Date,start_date:new Date,end_date:new Date,duration:0,required:!1},a.addemailattendee=function(b){a.attendees.push({email:b}),a.attendeeemail=""},a.loadcontacts=function(){d.loggedIn||f.path("login"),b({url:e.ip+"api/v1.0/User/getContactsInfo",method:"GET",withCredentials:!0}).then(function(b){for(var c=0;c<b.data.length;c++)void 0!==b.data[c].name&&void 0!==b.data[c].email&&a.usercontacts.push({name:b.data[c].name,email:b.data[c].email})},function(a){console.log(a)})},a.addcontacttoattendees=function(b){a.attendees.push({name:b.name}),a.usercontacts=a.usercontacts.filter(function(a){return b.name!==a.name})},a.removefromattendees=function(b){void 0!==b.groupname&&(a.groups.push(b),a.attendees=a.attendees.filter(function(a){return a.groupname!==b.groupname})),void 0!==b.name?(a.usercontacts.push(b),a.attendees=a.attendees.filter(function(a){return a.name!==b.name})):void 0!==b.email&&(a.attendees=a.attendees.filter(function(a){return a.email!==b.email}))},a.validateconstraintfields=function(){return a.constraints.start_date>a.constraints.end_date?!1:a.constraints.start_time>=a.constraints.end_time?!1:0===a.constraints.duration?!1:!0},a.loadgroups=function(){b({url:e.ip+"api/v1.0/Group/getGroupNames",method:"GET",withCredentials:!0}).then(function(b){b.data.forEach(function(b,c,d){a.groups.push({groupname:b.name})})},function(a){console.log(a)})},a.addgrouptoattendees=function(b){a.attendees.push(b),a.groups=a.groups.filter(function(a){return b.groupname!==a.groupname})},a.findmeetingtimes=function(a,b){b.forEach(function(a,b,c){console.log(a)})}}]).filter("attendeesValue",function(){return function(a){return a[Object.keys(a)[0]]}}),angular.module("easyncApp").controller("ScheduledCtrl",["$scope","$http","GlobalIPService",function(a,b,c){a.loadMeetings=function(){b({url:c.ip+"api/v1.0/User/getMeetings",method:"GET",withCredentials:!0}).then(function(a){console.log(a.data)},function(a){console.log(a)})}}]),angular.module("easyncApp").controller("GlobalWindowCtrl",["$scope","LoggedInService","$location",function(a,b,c){a.userLoggedIn=b.loggedIn,a.logOut=function(){b.logout()}}]),angular.module("easyncApp").controller("SettingsCtrl",["$scope","$http","GlobalIPService",function(a,b,c){a.contacts=[],a.calConnected=!1,a.addContact=!1,a.newContactError=!1,a.loadUser=function(){b({method:"GET",url:c.ip+"api/v1.0/Group/getUsers",withCredentials:!0}).then(function(a){for(var b in a.data){var c=a.data[b].name;console.log(c)}},function(a){console.log(a)})},a.loadContacts=function(){b({url:c.ip+"api/v1.0/User/getContactsInfo",method:"GET",withCredentials:!0}).then(function(b){for(var c=0;c<b.data.length;c++)void 0!==b.data[c].name&&void 0!==b.data[c].email&&a.contacts.push({name:b.data[c].name,email:b.data[c].email})},function(a){console.log(a)})},a.checkCalendar=function(){b({url:c.ip+"api/v1.0/User/hasConnectedGoogleCal",method:"GET",withCredentials:!0}).then(function(b){b.data===!0&&(a.calConnected=!0)},function(a){console.log("error fetching whether cal exists",a)})},a.connectCal=function(){b.jsonp(c.ip+"api/v1.0/User/addGoogleCal").then(function(a){},function(a){console.log("error connecting google cal",a)})},a.toggleAddContact=function(){a.addContact?a.addContact=!1:a.addContact=!0},a.handleNewContact=function(d){if(a.newContactError=!1,13===d.keyCode){var e=a.newContact;b({method:"POST",url:c.ip+"api/v1.0/User/addContactsCheckEmail",data:JSON.stringify({email:e}),withCredentials:!0}).then(function(b){void 0!==b.data.name?(a.contacts.push({name:b.data.name,email:b.data.email}),a.newContact=""):"no user found for email"==b.data&&(a.newContactError=!0)},function(a){console.log(a)})}}}]),angular.module("easyncApp").controller("GroupsCtrl",["$scope","$http","GlobalIPService",function(a,b,c){a.groups={},a.selectedgroup=[],a.groupname="",a.newRow=!1,a.contacts=[],a.loadGroups=function(){b({method:"GET",url:c.ip+"api/v1.0/Group/getGroups",withCredentials:!0}).then(function(b){for(var c in b.data){var d=b.data[c].groupName;a.groups[d]||(a.groups[d]=[]),a.groups[d].push({name:b.data[c].name,email:b.data[c].email})}},function(a){console.log(a)})},a.changeGoup=function(b,c){a.selectedgroup=b,a.groupname=c},a.addrow=function(){a.newRow=!0},a.saverow=function(d,e,f){f=JSON.parse(f);var g=function(g){b({method:"POST",url:c.ip+"api/v1.0/Group/addContactToGroup",data:g,withCredentials:!0}).then(function(b){"Person Added to Group"===b.data&&(a.newRow=!1,f?a.selectedgroup.push({name:f.name,email:f.email}):a.selectedgroup.push({name:d,email:e}),a.groups[a.groupname]=a.selectedgroup)},function(a){console.log(a)})};if(void 0!==f){var h={name:f.name,email:f.email,groupName:a.groupname},i=JSON.stringify(h);g(i)}else if(""!==d&&""!==e){var j={email:e};b({method:"GET",url:c.ip+"api/v1.0/User/exists",params:j,withCredentials:!0}).then(function(b){if(b.data){var c={name:d,email:e,groupName:a.groupname},f=JSON.stringify(c);g(f)}else console.log("user does not exist",b.data)},function(a){console.log(a)})}},a.loadcontacts=function(){b({url:c.ip+"api/v1.0/User/getContactsInfo",method:"GET",withCredentials:!0}).then(function(b){for(var c=0;c<b.data.length;c++)void 0!==b.data[c].name&&void 0!==b.data[c].email&&a.contacts.push({name:b.data[c].name,email:b.data[c].email})},function(a){console.log(a)})}}]),angular.module("easyncApp").run(["$templateCache",function(a){a.put("views/about.html",'<div> <h1>About Easync</h1> <div>Easync was created to help it\'s users find meeting times for a group as efficiently as possible. Something about picking time. Something about saving groups. </div> <h2>Easync redefining the way you connect.</h2> <h1>The Crayala Team</h1> <h2>Databases</h2> <ul class="img-list"> <li> <a href="http://ericsmithrocks.com"> <img src="/images/eric.69640a3f.jpg" width="150" height="150"> <span class="text-content"><span>Tendies</span></span> </a> </li> <li> <a href="http://alpacas.com"> <img src="/images/chris.00e5b2fa.jpg" width="150" height="150"> <span class="text-content"><span>Git Jesus</span></span> </a> </li> <li> <a href="https://www.youtube.com/watch?v=uRPKbAj4cuQ"> <img src="/images/jayce.75982085.jpg" width="150" height="150"> <span class="text-content"><span>J-ICE</span></span> </a> </li> </ul> <h2>GUI</h2> <ul class="img-list"> <li> <a href="https://twitter.com/cakeslap"> <img src="/images/gavin.d246fff1.jpg" width="150" height="150"> <span class="text-content"><span>Even dreams have seams</span></span> </a> </li> <li> <a href="https://twitter.com/mdzingo"> <img src="/images/morgan.3f7b3b03.png" width="150" height="150"> <span class="text-content"><span>If I could major in Alpacas I would</span></span> </a> </li> <li> <a href="http://jorgev.me"> <img src="/images/jorge.351ff6fb.jpg" width="150" height="150"> <span class="text-content"><span>I do great shit.</span></span> </a> </li> <li> <a href="https://www.facebook.com/samwhunter"> <img src="/images/sam.f27e1012.png" width="150" height="150"> <span class="text-content"><span>Do it for Bub.</span></span> </a> </li> </ul> </div> <style>h1,h2{\n	 text-align: center;\n}\ndiv{\n	font-family: \'Raleway\', sans-serif;\n	text-align: center;\n}\n\nul.img-list {\n  list-style-type: none;\n  margin: 0;\n  padding: 0;\n  text-align: center;\n}\n\nul.img-list li {\n  display: inline-block;\n  height: 150px;\n  margin: 0 1em 1em 0;\n  position: relative;\n  width: 150px;\n}\n\nspan.text-content {\n  background: rgba(0,0,0,0.5);\n  color: white;\n  cursor: pointer;\n  display: table;\n  height: 150px;\n  left: 0;\n  position: absolute;\n  top: 0;\n  width: 150px;\n}\n\nspan.text-content span {\n  display: table-cell;\n  text-align: center;\n  vertical-align: middle;\n}\n\nspan.text-content {\n  background: rgba(0,0,0,0.5);\n  color: white;\n  cursor: pointer;\n  display: table;\n  height: 150px;\n  left: 0;\n  position: absolute;\n  top: 0;\n  width: 150px;\n  opacity: 0;\n}\n\nul.img-list li:hover span.text-content {\n  opacity: 1;\n}\n\n\nspan.text-content {\n  background: rgba(0,0,0,0.5);\n  color: white;\n  cursor: pointer;\n  display: table;\n  height: 150px;\n  left: 0;\n  position: absolute;\n  top: 0;\n  width: 150px;\n  opacity: 0;\n  -webkit-transition: opacity 500ms;\n  -moz-transition: opacity 500ms;\n  -o-transition: opacity 500ms;\n  transition: opacity 500ms;\n}</style>'),a.put("views/devprocess.html",""),a.put("views/groups.html",'<div class="row row-centered" ng-controller="GroupsCtrl as groups"> <h2>Groups</h2> <div class="col-md-3 col-centered" ng-init="loadGroups()"> <table class="table table-hover" id="grouplisttable"> <thead> <tr> <th>Group Name</th> <th>Number of Members</th> </tr> </thead> <tbody> <tr ng-repeat="(key, value) in groups" ng-click="changeGoup(value, key)"> <td> {{ key }} </td> <td> {{ value.length }} </td> </tr> </tbody> </table> </div> <div class="col-md-5 col-centered" ng-show="selectedgroup.length > 0"> <h3> {{ groupname }} <a ng-show="!newRow" ng-click="addrow()"><span class="glyphicon glyphicon-plus pull-right"> </span></a> <a ng-show="newRow" ng-click="saverow(newName, newEmail, newContact)"><span class="glyphicon glyphicon-floppy-save pull-right"></span></a> </h3> <table class="table table-hover"> <thead> <tr> <th> Name </th> <th> Email </th> </tr> </thead> <tbody id="groupDetails"> <tr ng-repeat="person in selectedgroup"> <td> {{ person.name }} </td> <td> {{ person.email }} </td> </tr> <tr ng-show="newRow == true"> <td><input class="form-control input-sm" ng-model="newName"></td> <td><input class="form-control input-sm" ng-model="newEmail"></td> </tr> <tr ng-show="newRow == true" ng-init="loadcontacts()"> <td>or add a contact</td> <td> <select class="form-control" ng-model="newContact"> <option ng-repeat="person in contacts" value="{{ person }}"> {{ person.name }} </option> </select> </td> </tr> </tbody> </table> </div> </div> <style>h2 {\n        text-align: center;\n    }\n\n    th {\n        font-weight: bold;\n    }\n\n    #grouplisttable td {\n        text-align: center;\n    }\n\n    .row-centered {\n        text-align:center;\n    }\n\n    .col-centered {\n        vertical-align: top;\n        display:inline-block;\n        float:none;\n        text-align:left;\n        margin-right:-4px;\n    }</style>'),a.put("views/login.html",'<div class="row marketing loginregrow"> <div class="col-md-8 log-in-cal"> <time datetime="2014-09-20" class="icon"> <em><span class="glyphicon glyphicon-ok color" aria-hidden="true"></span></em> <strong>Log In</strong> </time> </div> <div class="col-md-4"> <div ng-init="check_cookies()" ng-controller="LoginCtrl" id="loginformscol"> <form name="loginform" class="css-form" id="loginform"> <input type="email" ng-model="email" placeholder="Email" id="emLog"><br> <input type="password" ng-model="pass" placeholder="Password" id="passLog"><br> <input type="submit" value="Log In" ng-click="login(email, pass)"> </form> <div id="googlesignin"> <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div> <a onclick="signout_google()">Sign out of google</a> </div> </div> </div> </div> <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script> <script>/*function onSuccess(googleUser) {\n      console.log(\'Logged in as: \' + googleUser.getBasicProfile().getName());\n    }\n    function onFailure(error) {\n      console.log(error);\n    }\n    function renderButton() {\n      gapi.signin2.render(\'my-signin2\', {\n'+"        'scope': 'https://www.googleapis.com/auth/plus.login',\n        'width': 200,\n        'height': 50,\n        'longtitle': true,\n        'theme': 'dark',\n        'onsuccess': onSuccess,\n        'onfailure': onFailure\n      });\n    }*/\n    function signout_google() {\n    	var auth2 = gapi.auth2.getAuthInstance();\n    	auth2.signOut().then(function () {\n      		console.log('User signed out.');\n    	});\n  	}\n\n    function onSignIn(googleUser) {\n      // Useful data for your client-side scripts:\n      var profile = googleUser.getBasicProfile();\n      console.log(\"ID: \" + profile.getId()); // Don't send this directly to your server!\n      console.log(\"Name: \" + profile.getName());\n      console.log(\"Image URL: \" + profile.getImageUrl());\n      console.log(\"Email: \" + profile.getEmail());\n\n      // The ID token you need to pass to your backend:\n      var id_token = googleUser.getAuthResponse().id_token;\n      console.log(\"ID Token: \" + id_token);\n\n      console.log(profile.getName().split(' ')[0], profile.getName().split(' ')[1]);\n\n      var data = {\n        'google_ID' : profile.getId(),\n        'firstname' : profile.getName().split(' ')[0],\n        'lastname' : profile.getName().split(' ')[1],\n        'email' : profile.getEmail()\n      };\n\n      var json_data = JSON.stringify(data);\n\n      $.post('http://localhost:6969/api/v1.0/User/googlesignin', function (data) {\n        console.log(data);\n      });\n    };</script> <style>h2 {\n		text-align: center;\n	}\n\n	#googlesignin {\n    margin-top: 30px;\n		text-align: center;\n	}\n\n	#my-signin2 > div {\n		width: 250px;\n		margin: 0 auto;\n	}\n  #emLog, #passLog{\n    width:250px;\n    margin: 10px;\n    height:30px;\n  }\n  #btn {\n    width: 250px;\n    margin: 10px;\n    height:30px;\n  }\n\n  input {\n    width: 250px;\n    margin-left: auto;\n    margin-right: auto;\n    height:30px;\n  }\n\n  #loginformscol div {\n    margin-left: auto;\n    margin-right: auto;\n  }\n\n  #loginform {\n    text-align: center;\n  }</style>"),a.put("views/main.html",'<div ng-init="check_cookies()" class="row circleButtons"> <ul class="img-list"> <li class="round-button"> <a ng-href="#/newmeeting"> <span class="glyphicon glyphicon-plus main-icon"></span><span class="text-content"><p>New Meeting</p></span> </a> </li> <li class="round-button"> <a ng-href="#/scheduled"> <span class="glyphicon glyphicon-calendar main-icon"></span><span class="text-content"><p>Scheduled</p></span> </a> </li> <li class="round-button"> <a ng-href="#/groups"> <span class="glyphicon glyphicon-glass main-icon"></span><span class="text-content"><p>Groups</p></span> </a> </li> </ul> </div> <script>var $x = $(\'.round-button > a > span.text-content > p\');\n$x.each(function(){\n  var pad = (200 - $(this).height())/2;\n  $(this).css(\'padding-top\',pad);\n});\nvar y = $(\'.round-button > a > span.glyphicon\');\ny.each(function(){\n  var pad2 = (200 - $(this).height())/2;\n  $(this).css(\'padding-top\',pad2);\n});</script>'),a.put("views/newmeeting.html",'<div class="container" ng-controller="NewMeetingCtrl as newmeeting"> <h1>Schedule a New Meeting</h1> <div class="row text-center" id="attendees"> <h3> Attendees </h3> <div class="col-md-3" ng-model="attendees"> <p>Currently Selected</p> <p ng-repeat="x in attendees" ng-click="removefromattendees(x)"> {{ x|attendeesValue }} </p> </div> <div class="col-md-3">Near Me</div> <div class="col-md-3" ng-init="loadcontacts()" ng-model="usercontacts"> <p>Add Contact</p> <p ng-repeat="x in usercontacts" ng-click="addcontacttoattendees(x)"> {{ x.name }} </p> </div> <div class="col-md-3" ng-model="usercontacts" ng-init="loadgroups()"> <p>Add Group</p> <p ng-repeat="x in groups" ng-click="addgrouptoattendees(x)"> {{ x.groupname }} </p> </div> </div> <div class="row text-center" id="addattendees"> <button class="btn btn-success" onclick="toggleEmailField()">Add Attendees by Email</button><br> <span id="addbyemailslide"> <input ng-model="attendeeemail" type="email"> <span ng-click="addemailattendee(attendeeemail)" class="glyphicon glyphicon-plus-sign"></span> </span> </div> <div class="row text-center" id="datetimeconstraints"> <h3> Date and Time Constraints</h3> <form name="datetimeform"> From <input name="fromdate" type="date" ng-model="constraints.start_date"> To <input name="todate" type="date" ng-model="constraints.end_date"><br> <span class="error" ng-show="constraints.start_date > constraints.end_date"> End date must be greater or same as start date </span><br> After <input name="aftertime" type="time" ng-model="constraints.start_time"> Before <input name="beforetime" type="time" ng-model="constraints.end_time"><br> <span class="error" ng-show="constraints.start_time >= constraints.end_time"> End time must be greater as start time </span><br> Length <select name="duration" ng-model="constraints.duration"> <option value="15"> 15 Minutes</option> <option value="30"> 30 Minutes</option> <option value="45"> 45 Minutes</option> <option value="60"> 1 Hour</option> <option value="120"> 2 Hours</option> </select> <label for="required">Attendance Required</label><input type="checkbox" name="required" id="required" ng-model="constraints.required"> </form> <button class="btn btn-info" ng-show="validateconstraintfields() === false" disabled>Find Possible Meeting Times</button> <button class="btn btn-info" ng-show="validateconstraintfields() === true" ng-click="findmeetingtimes(constraints, attendees)">Find Possible Meeting Times</button> </div> <div class="row text-center" id="possibletimes" ng-show="possibletimes_bool"> <h3> Possible Times</h3> </div> </div> <script>function toggleEmailField() {\n	var inputarea = document.getElementById("addbyemailslide");\n'+"	if ($(inputarea).css('display') === 'none') \n		$(inputarea).slideDown();\n	else \n		$(inputarea).slideUp();\n}</script> <style>h1 {\n		text-align: center;\n	}\n\n	select {\n		margin-right: 10px;\n	}\n\n	input, select {\n		margin-top: 20px;\n	}\n\n	div div.row {\n		margin-top: 20px;\n		padding-bottom: 15px;\n	}\n\n	#addattendees {\n		margin-top: 0px;\n	}\n\n\n	#attendees, #addattendees {\n		background-color: lightblue;\n	}\n\n	#datetimeconstraints {\n		background-color: lightgreen;\n	}\n\n	#possibletimes {\n		background-color: lightblue;\n	}\n\n	#addbyemailslide {\n		display: none;\n	}\n	\n	button {\n		margin: auto;\n		display: table;\n		margin-top: 30px;\n	}</style>"),a.put("views/register.html",'<div class="row loginregrow"> <div class="col-md-8 register-cal"> <time datetime="2014-09-20" class="icon"> <em><span class="glyphicon glyphicon-ok color" aria-hidden="true"></span></em> <strong>Register</strong> </time> </div> <div class="col-md-4"> <div ng-controller="RegisterCtrl as register"> <form name="form" novalidate class="css-form"> <input type="text" ng-model="user.firstname" required placeholder="First Name"> <br> <input type="text" ng-model="user.lastname" required placeholder="Last Name"> <br> <input type="email" ng-model="user.email" name="email" required placeholder="Email"> <br> <div ng-show="form.$submitted"> <div ng-show="form.email.$error.email">Not a valid email!</div> </div> <input type="password" ng-model="user.pass" required id="pass1" name="pass1" placeholder="Password"> <br> <input type="password" name="pass2" ng-model="user.passtwo" pwcheck="pass1" required placeholder="Confirm Password"> <br> <div ng-show="form.pass2.$error"> <div ng-show="form.pass2.$error.pwcheck">Passwords must be identical</div> </div> <input ng-show="form.$valid" type="submit" ng-click="register.register(user)" value="Sign Up" name="submit"> <input ng-show="!form.$valid" type="submit" value="Sign Up" name="submit" disabled> </form> </div> </div> </div> <style>.css-form input[disabled] {\n		color: red;\n	}\n	input {\n		width: 250px;\n		margin:10px;\n		height:30px;\n	}</style>'),a.put("views/scheduled.html",'<div class="row"> <h2>Scheduled Meetings</h2> <table id="scheduledMeetings" ng-init="loadMeetings()"> <tr ng-repeat="x in meetings"> <td>{{x.name}}</td> <td>{{x.location}}</td> <td>{{x.startTime}}</td> <td>{{x.attendees}}</td> </tr> </table> </div> <style>h2 {\n		text-align: center;\n		font-weight: 300;\n		color:#1B998B;\n	}</style>'),a.put("views/settings.html",'<div class="userName" ng-init="getUserDetails()"> <h1 class="name">A name is going to go here</h1> <h1 ng-repeat="u in user" class="name">{{u.name}}</h1> </div> <div class="row loginregrow" ng-controller="SettingsCtrl as settings"> <div class="col-md-6 contactsCol"> <div class="contactsList" ng-init="loadContacts()"> <p ng-repeat="x in contacts"> {{ x.name }} </p> </div> <div ng-show="addContact"> <input class="form-control input-sm add-contact" placeholder="email" ng-keypress="handleNewContact($event)" ng-model="newContact"> </div> <div class="alert alert-danger" ng-show="newContactError" role="alert"><strong>Error:</strong> email not registered with Easync</div> <button class="btn" ng-click="toggleAddContact()"> Add Contact </button> </div> <div class="col-md-6 addCalCol" ng-init="checkCalendar()"> <button class="btn" ng-show="calConnected" disabled> Connect Google Calendar </button> <button class="btn" ng-show="!calConnected" ng-click="connectCal()"> Connect Google Calendar </button> </div> </div> <style>.name{\n	text-align:left;\n	color:#1B998B;\n}</style>'),a.put("views/techoverview.html","")}]);