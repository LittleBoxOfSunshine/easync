"use strict";angular.module("easyncApp",["ngAnimate","ngCookies","ngResource","ngRoute","ngSanitize","ngTouch","ui.calendar"]).config(["$routeProvider",function(a){a.when("/",{templateUrl:"views/main.html",controller:"MainCtrl",controllerAs:"main"}).when("/register",{templateUrl:"views/register.html",controller:"RegisterCtrl",controllerAs:"register"}).when("/login",{templateUrl:"views/login.html",controller:"LoginCtrl",controllerAs:"login"}).when("/calendar",{templateUrl:"views/calendar.html",controller:"CalendarCtrl",controllerAs:"calendar"}).when("/scheduled",{templateUrl:"views/scheduled.html",controller:"ScheduledCtrl",controllerAs:"scheduled"}).when("/newmeeting",{templateUrl:"views/newmeeting.html",controller:"NewMeetingCtrl",controllerAs:"newmeeting"}).when("/groups",{templateUrl:"views/groups.html",controller:"GroupsCtrl",controllerAs:"groups"}).when("/about",{templateUrl:"views/about.html",controller:"AboutCtrl",controllerAs:"about"}).when("/settings",{templateUrl:"views/settings.html",controller:"SettingsCtrl",controllerAs:"settings"}).when("/gbwindow",{templateUrl:"views/main.html",controller:"GlobalWindowCtrl",controllerAs:"windowcontrol"}).when("/techoverview",{templateUrl:"views/techoverview.html",controller:"TechOverviewCtrl",controllerAs:"techoverview"}).when("/devprocess",{templateUrl:"views/devprocess.html",controller:"DevProcessCtrl",controllerAs:"devprocess"}).otherwise({redirectTo:"/"})}]).factory("LoggedInService",["$cookies","$location",function(a,b){return{loggedIn:function(){return a.get("easync_logged")?!0:!1},set_or_refresh_cookie:function(b){var c=new Date((new Date).valueOf()+18e5);return a.put("easync_logged","true",{expires:c}),void 0!==typeof b&&a.put("easync_email",b,{expires:c}),!0},logout:function(){return this.loggedIn()?(a.remove("easync_logged"),a.remove("slim_session")):console.log("tried to log out, but wasn't logged in"),b.path("login"),!0}}}]).factory("GlobalIPService",function(){return{ip:"http://easync.com/"}}).config(["$httpProvider",function(a){a.defaults.headers.common={},a.defaults.headers.post={},a.defaults.headers.put={},a.defaults.headers.patch={}}]),angular.module("easyncApp").controller("MainCtrl",["$scope","$http","$cookies","LoggedInService","$location",function(a,b,c,d,e){a.showNewMeeting=!0,a.check_cookies=function(){d.loggedIn()||e.path("login")}}]),angular.module("easyncApp").controller("RegisterCtrl",["$scope","$http","GlobalIPService","LoggedInService","$location",function(a,b,c,d,e){a.user={firstname:"",lastname:"",email:"",pass:"",passtwo:""},this.errors=[],this.register=function(e){this.errors=[],console.log(e);for(var f in e)(""===e[f]||"undefined"==typeof e[f])&&this.errors.push("new error");if(0===this.errors.length){var g={email:e.email,password:e.pass,firstname:e.firstname,lastname:e.lastname},h=JSON.stringify(g);console.log(h),b.post(c.ip+"api/v1.0/User/register",h).success(function(f){if(console.log(f),"Account Created."===f){var g={email:e.email,password:e.pass},h=JSON.stringify(g);a.user={firstname:"",lastname:"",email:"",pass:"",passtwo:""},b({url:c.ip+"api/v1.0/User/login",method:"POST",data:h,withCredentials:!0}).then(function(a){console.log(a.data),"Login successful"===a.data&&(d.set_or_refresh_cookie(e.email),window.location=c.ip+"api/v1.0/User/addGoogleCal")},function(a){console.log(a)})}else console.log("error creating account")}).error(function(a){console.log("Register failed "+a)})}}}]),angular.module("easyncApp").directive("pwcheck",function(){return{require:"ngModel",link:function(a,b,c,d){var e="#"+c.pwcheck;b.add(e).on("keyup",function(){a.$apply(function(){var a=b.val()===$(e).val();d.$setValidity("pwcheck",a)})})}}}),angular.module("easyncApp").controller("LoginCtrl",["$scope","$http","$cookies","GlobalIPService","LoggedInService","$location",function(a,b,c,d,e,f){a.userLoggedIn=!1,a.email="",a.pass="",a.login=function(c,g){var h={email:c,password:g},i=JSON.stringify(h);b({url:d.ip+"api/v1.0/User/login",method:"POST",data:i,withCredentials:!0}).then(function(b){"Login successful"===b.data&&(a.email="",a.pass="",e.set_or_refresh_cookie(c),f.path(""))},function(b){console.log(b),a.pass=""})},a.check_cookies=function(){e.loggedIn()&&(console.log("redirecting to dashboard"),f.path(""))}}]),angular.module("easyncApp").controller("NewMeetingCtrl",["$scope","$http","$cookies","LoggedInService","GlobalIPService","$location",function(a,b,c,d,e,f){a.possibletimes_bool=!1,a.attendees={emails:[],users:[],groups:[]},a.usercontacts=[],a.groups=[],a.constraints={start_time:new Date,end_time:new Date,start_date:new Date,end_date:new Date,duration:0,required:!1,title:"",location:"",description:"",attachment:""},a.addemailattendee=function(b){a.attendees.emails.push({email:b}),a.attendeeemail=""},a.loadcontacts=function(){d.loggedIn||f.path("login"),b({url:e.ip+"api/v1.0/User/getContactsInfo",method:"GET",withCredentials:!0}).then(function(b){for(var c=0;c<b.data.length;c++)void 0!==b.data[c].name&&void 0!==b.data[c].email&&a.usercontacts.push({name:b.data[c].name,email:b.data[c].email})},function(a){console.log(a)})},a.addcontacttoattendees=function(b){a.attendees.users.push(b),a.usercontacts=a.usercontacts.filter(function(a){return b.name!==a.name})},a.removefromattendees=function(b){void 0!==b.groupname&&(a.groups.push(b),a.attendees.groups=a.attendees.groups.filter(function(a){return a.groupname!==b.groupname})),void 0!==b.name?(a.usercontacts.push(b),a.attendees.users=a.attendees.users.filter(function(a){return a.name!==b.name})):void 0!==b.email&&(a.attendees.emails=a.attendees.emails.filter(function(a){return a.email!==b.email}))},a.validateconstraintfields=function(){return a.constraints.start_date>a.constraints.end_date?!1:a.constraints.start_time>=a.constraints.end_time?!1:0===a.constraints.duration?!1:""===a.constraints.title||"Meeting Name"===a.constraints.title?!1:!0},a.loadgroups=function(){b({url:e.ip+"api/v1.0/Group/getGroupNames",method:"GET",withCredentials:!0}).then(function(b){b.data.forEach(function(b,c,d){a.groups.push({groupname:b.name})})},function(a){console.log(a)})},a.addgrouptoattendees=function(b){a.attendees.groups.push(b),a.groups=a.groups.filter(function(a){return b.groupname!==a.groupname})},a.findmeetingtimes=function(a,d){var f={emails:[],EventDetails:{}},g=function(a){var b=parseInt(60*a,10),c=Math.floor(b/3600),d=Math.floor((b-3600*c)/60),e=Math.floor(b-3600*c-60*d);10>c&&(c="0"+c),10>d&&(d="0"+d),10>e&&(e="0"+e);var f=c+":"+d+":"+e;return f},h=function(a){var b=a.getHours(),c=a.getMinutes(),d=a.getSeconds();10>b&&(b="0"+b),10>c&&(c="0"+c),10>d&&(d="0"+d);var e=b+":"+c+":"+d;return e},i=function(a,b){var c=a.getFullYear(),d=a.getDate(),e=a.getMonth()+1;10>c&&(c="0"+c),10>e&&(e="0"+e),10>d&&(d="0"+d);var f=c+"-"+e+"-"+d;return b?f+" 00:00:00":f+" 23:59:59"};f.allRequired=a.required,f.dayStart=h(a.start_time),f.dayEnd=h(a.end_time),f.EventDetails.startTime=i(a.start_date,!0),f.EventDetails.endTime=i(a.end_date,!1),f.EventDetails.creatorEmail=c.get("easync_email"),f.EventDetails.name=a.title,f.EventDetails.description=a.description,f.EventDetails.location=a.location,f.EventDetails.attachments=a.attachment;var j=g(a.duration);f.length=j,d.emails.forEach(function(a,b,c){f.emails.push(a.email)}),d.users.forEach(function(a,b,c){f.emails.push(a.email)});var k=function(a){a.data.forEach(function(a,b,c){f.emails.push(a.email)}),f.emails.push(c.get("easync_email"));var d=f.emails.length;f.emails=f.emails.filter(function(a,b,c){return c.indexOf(a)===b}),console.log("emails trimmed from "+d+" to "+f.emails.length+" email addresses"),console.log(f),b({method:"POST",url:e.ip+"api/v1.0/Meeting/planMeeting",withCredentials:!0,data:JSON.stringify(f)}).then(function(a){console.log(a.data)},function(a){console.log(a)})},l=d.groups.map(function(a){return a.groupname});b({method:"POST",url:e.ip+"api/v1.0/Group/getGroupContents",withCredentials:!0,data:JSON.stringify(l)}).then(k,function(a){console.log(a)})}}]).filter("attendeesValue",function(){return function(a){return void 0!==a.groupname?a.groupname:void 0!==a.name?a.name:a.email}}),angular.module("easyncApp").controller("ScheduledCtrl",["$scope","$http","GlobalIPService",function(a,b,c){a.loadMeetings=function(){b({url:c.ip+"api/v1.0/User/getMeetings",method:"GET",withCredentials:!0}).then(function(a){console.log(a.data)},function(a){console.log(a)})}}]),angular.module("easyncApp").controller("GlobalWindowCtrl",["$scope","LoggedInService","$location",function(a,b,c){a.userLoggedIn=b.loggedIn,a.logOut=function(){b.logout()}}]),angular.module("easyncApp").controller("SettingsCtrl",["$scope","$http","GlobalIPService","$location","$window",function(a,b,c,d,e){a.contacts=[],a.calConnected=!1,a.addContact=!1,a.newContactError=!1,a.name="",a.loadUser=function(){b({method:"GET",url:c.ip+"api/v1.0/User/getUserDetails",withCredentials:!0}).then(function(b){a.name=b.data.name},function(a){console.log(a)})},a.loadContacts=function(){b({url:c.ip+"api/v1.0/User/getContactsInfo",method:"GET",withCredentials:!0}).then(function(b){for(var c=0;c<b.data.length;c++)void 0!==b.data[c].name&&void 0!==b.data[c].email&&a.contacts.push({name:b.data[c].name,email:b.data[c].email})},function(a){console.log(a)})},a.checkCalendar=function(){b({url:c.ip+"api/v1.0/User/hasConnectedGoogleCal",method:"GET",withCredentials:!0}).then(function(b){b.data===!0&&(a.calConnected=!0)},function(a){console.log("error fetching whether cal exists",a)})},a.connectCal=function(){e.location.href(c.ip+"api/v1.0/User/addGoogleCal")},a.toggleAddContact=function(){a.addContact?a.addContact=!1:a.addContact=!0},a.handleNewContact=function(d){if(a.newContactError=!1,13===d.keyCode){var e=a.newContact;b({method:"POST",url:c.ip+"api/v1.0/User/addContactsCheckEmail",data:JSON.stringify({email:e}),withCredentials:!0}).then(function(b){void 0!==b.data.name?(a.contacts.push({name:b.data.name,email:b.data.email}),a.newContact=""):"no user found for email"===b.data&&(a.newContactError=!0)},function(a){console.log(a)})}},a.removeContact=function(d){b({method:"GET",url:c.ip+"api/v1.0/User/removeContact",withCredentials:!0,params:{email:d.email}}).then(function(b){console.log(b.data),"Contact Removed"===b.data&&(a.contacts=a.contacts.filter(function(a){return a.email!==d.email}))},function(a){console.log(a)})}}]),angular.module("easyncApp").controller("GroupsCtrl",["$scope","$http","GlobalIPService",function(a,b,c){a.groups={},a.selectedgroup=[],a.groupname="",a.newRow=!1,a.contacts=[],a.changeColor=function(b,c){c===!0?a.personColour={color:"#FF5252"}:c===!1&&(a.personColour={color:"#F9F9F9"})},a.loadGroups=function(){b({method:"GET",url:c.ip+"api/v1.0/Group/getGroups",withCredentials:!0}).then(function(b){for(var c in b.data){var d=b.data[c].groupName;a.groups[d]||(a.groups[d]=[]),a.groups[d].push({name:b.data[c].name,email:b.data[c].email})}},function(a){console.log(a)})},a.changeGoup=function(b,c){a.selectedgroup=b,a.groupname=c},a.addrow=function(){a.newRow=!0},a.saverow=function(d,e,f){f=JSON.parse(f);var g=function(g){b({method:"POST",url:c.ip+"api/v1.0/Group/addContactToGroup",data:g,withCredentials:!0}).then(function(b){"Person Added to Group"===b.data&&(a.newRow=!1,f?a.selectedgroup.push({name:f.name,email:f.email}):a.selectedgroup.push({name:d,email:e}),a.groups[a.groupname]=a.selectedgroup)},function(a){console.log(a)})};if(void 0!==f){var h={name:f.name,email:f.email,groupName:a.groupname},i=JSON.stringify(h);g(i)}else if(""!==d&&""!==e){var j={email:e};b({method:"GET",url:c.ip+"api/v1.0/User/exists",params:j,withCredentials:!0}).then(function(b){if(b.data){var c={name:d,email:e,groupName:a.groupname},f=JSON.stringify(c);g(f)}else console.log("user does not exist",b.data)},function(a){console.log(a)})}},a.loadcontacts=function(){b({url:c.ip+"api/v1.0/User/getContactsInfo",method:"GET",withCredentials:!0}).then(function(b){for(var c=0;c<b.data.length;c++)void 0!==b.data[c].name&&void 0!==b.data[c].email&&a.contacts.push({name:b.data[c].name,email:b.data[c].email})},function(a){console.log(a)})}}]),angular.module("easyncApp").run(["$templateCache",function(a){a.put("views/about.html",'<div> <h1>About Easync</h1> <div>The idea for Easync came from its developers discussing the next time they could meet. A bit problematic, no one seemed to be able to come up with the perfect date and time. "Wait, there should be an app for that!", they thought, and set out to create the perfect group scheduling application. </div> <h2>Easync redefining the way you connect.</h2> <h1>The Crayala Team</h1> <h2>Databases</h2> <ul class="img-list"> <li> <a href="http://ericsmithrocks.com"> <img src="/images/eric.69640a3f.jpg" width="150" height="150"> <span class="img-content"><span>Tendies</span></span> </a> </li> <li> <a href="http://alpacas.com"> <img src="/images/chris.00e5b2fa.jpg" width="150" height="150"> <span class="img-content"><span>Git Jesus</span></span> </a> </li> <li> <a href="https://www.youtube.com/watch?v=uRPKbAj4cuQ"> <img src="/images/jayce.75982085.jpg" width="150" height="150"> <span class="img-content"><span>J-ICE</span></span> </a> </li> </ul> <h2>GUI</h2> <ul class="img-list"> <li> <a href="https://twitter.com/cakeslap"> <img src="/images/gavin.d246fff1.jpg" width="150" height="150"> <span class="img-content"><span>Even dreams have seams</span></span> </a> </li> <li> <a href="https://twitter.com/mdzingo"> <img src="/images/morgan.3f7b3b03.png" width="150" height="150"> <span class="img-content"><span>If I could major in Alpacas I would</span></span> </a> </li> <li> <a href="http://jorgev.me"> <img src="/images/jorge.351ff6fb.jpg" width="150" height="150"> <span class="img-content"><span>I do great shit.</span></span> </a> </li> <li> <a href="https://www.facebook.com/samwhunter"> <img src="/images/sam.f27e1012.png" width="150" height="150"> <span class="img-content"><span>Do it for Bub.</span></span> </a> </li> </ul> </div> <style></style>'),a.put("views/devprocess.html",'<div>\n	<h1>Development Process</h1>\n		<div>\n		The initial vision of our project was as such: the ability to put\n		multiple phones close together and have our application produce\n		the ideal time for our users to meet. \n		</div>\n\n	<h2>Features</h2>\n		<div>\n		Most of what we set out to accomplish made its way into our application. \n		</div>\n		<br>\n		<br>\n		<h4>What was cut</h4>\n		<ul class="devPro">\n			<li>Traditional Group functionality</li>\n			<li>Google Hangouts integration</li>\n			<li>Note-taking features</li>\n			<li>Meeting Timers</li>\n		</ul>\n		\n		<br>\n		<h4>What made it</h4>\n		<ul class="devPro">\n			<li>Google Calendar integration</li>\n			<li>Google login</li>\n			<li>Custom login</li>\n			<li>Ideal meeting algorithm</li>\n			<li>Group aliasing</li>\n			<li>Nearby meeting token</li>\n			<li>Email confirmation</li>\n		</ul>\n		\n\n	<h2>Initial Designs</h2>\n	\n		<img src="/images/proto1.63875edf.png" width="250" height="250"/>\n		<img src="/images/proto2.302e6b64.png" width="250" height="250"/><br>\n		<img src="/images/proto3.acf4bd45.png" width="250" height="250"/>\n		<img src="/images/proto4.20d8b263.png" width="250" height="250"/>\n	\n	<h2>Hurdles</h2>\n	<div>\n		Our team worked surprisingly well together, but that\'s not to say there weren\'t challenges to overcome. Most problems arose from communication and an inability to \n		express our personal desires for the project. Several times we had to get together \n		and reiterate the team\'s goals.\n		<br><br>\n		There definitely were some technical hurdles as well. These were some of the things our team found to be the most difficult.\n\n		<ul class="devPro">\n			<li>Google OAuth 2</li>\n			<li>Android Studio</li>\n			<li>Formatting dates</li>\n			<li>Doing time calculations</li>\n		</ul>\n	</div>\n	\n	<h2>Wishlist</h2>\n	<div>We got most of what we wanted, but here\'s some things we\'d like to see in v2.0:</div>\n	<ul class="devPro">\n		<li>Google Nearby API</li>\n		<li>A visual calendar</li>\n		<li>Microsoft and Apple Calendar integration</li>\n		<li>Multiple calendars for each person</li>\n	</ul>\n</div>\n\n<style>\n\n\nh1, h4{\n	color:#FF5252;\n	text-align:center;\n}\n\nimg{\n	width:250;\n	height:250;\n	margin:25px;\n}\nli{\n	margin-left:-30px;\n}\n\n.devPro {\n	text-decoration: none;\n	list-style-type: none;\n	text-align: center;\n\n}\n<style>'),a.put("views/groups.html",'<div class="row row-centered" ng-controller="GroupsCtrl as groups"> <h2>Groups</h2> <div class="col-md-3 col-centered" ng-init="loadGroups()"> <table class="table table-hover hoverTable" id="grouplisttable"> <thead> <tr> <th>Group Name</th> <th>Number of Members</th> </tr> </thead> <tbody> <tr ng-repeat="(key, value) in groups" ng-click="changeGoup(value, key)" id="godHelpME" ng-style="color" ng-mouseenter="changeColor(i,true)" ng-mouseleave="changeColor(i,false)"> <td> {{ key }} </td> <td> {{ value.length }} </td> </tr> </tbody> </table> </div> <div class="col-md-5 col-centered" ng-show="selectedgroup.length > 0"> <h3> {{ groupname }} <a ng-show="!newRow" ng-click="addrow()"><span class="glyphicon glyphicon-plus pull-right"> </span></a> <a ng-show="newRow" ng-click="saverow(newName, newEmail, newContact)"><span class="glyphicon glyphicon-floppy-save pull-right"></span></a> </h3> <table class="table table-hover hoverTable"> <thead> <tr> <th> Name </th> <th> Email </th> </tr> </thead> <tbody id="groupDetails"> <tr ng-repeat="person in selectedgroup"> <td> {{ person.name }} </td> <td> {{ person.email }} </td> </tr> <tr ng-show="newRow == true"> <td><input class="form-control input-sm" ng-model="newName"></td> <td><input class="form-control input-sm" ng-model="newEmail"></td> </tr> <tr ng-show="newRow == true" ng-init="loadcontacts()"> <td>or add a contact</td> <td> <select class="form-control" ng-model="newContact"> <option ng-repeat="person in contacts" value="{{ person }}"> {{ person.name }} </option> </select> </td> </tr> </tbody> </table> </div> </div>'),a.put("views/login.html",'<div class="row marketing loginregrow"> <div class="col-md-8 log-in-cal"> <time datetime="2014-09-20" class="icon"> <em><span class="glyphicon glyphicon-ok color" aria-hidden="true"></span></em> <strong>Log In</strong> </time> </div> <div class="col-md-4"> <div ng-init="check_cookies()" ng-controller="LoginCtrl" id="loginformscol"> <form name="loginform" class="css-form" id="loginform"> <input type="email" ng-model="email" placeholder="Email" id="emLog"><br> <input type="password" ng-model="pass" placeholder="Password" id="passLog"><br> <input type="submit" value="Log In" ng-click="login(email, pass)"> </form> <div id="googlesignin"> <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark" id="googleSignInButton"></div> <a onclick="signout_google()">Sign out of google</a> </div> </div> </div> </div> <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script> <script>function signout_google() {\n    	var auth2 = gapi.auth2.getAuthInstance();\n    	auth2.signOut().then(function () {\n      		console.log(\'User signed out.\');\n    	});\n  	}\n\n    function onSignIn(googleUser) {\n      // Useful data for your client-side scripts:\n      var profile = googleUser.getBasicProfile();\n      /*console.log("ID: " + profile.getId()); // Don\'t send this directly to your server!\n      console.log("Name: " + profile.getName());\n      console.log("Image URL: " + profile.getImageUrl());\n      console.log("Email: " + profile.getEmail());*/\n\n      // The ID token you need to pass to your backend:\n      var id_token = googleUser.getAuthResponse().id_token;\n      //console.log("ID Token: " + id_token);\n\n'+"      console.log(profile.getName().split(' ')[0], profile.getName().split(' ')[1]);\n\n      var data = {\n        'google_ID' : profile.getId(),\n        'firstname' : profile.getName().split(' ')[0],\n        'lastname' : profile.getName().split(' ')[1],\n        'email' : profile.getEmail(),\n        'google_token' : id_token\n      };\n\n      var json_data_global = JSON.stringify(data);\n    \n      $.post('http://easync.com/api/v1.0/User/googleSignIn', json_data_global).done(function(response) {\n        //window.location = 'http://easync.com/api/v1.0/User/addGoogleCal';\n        setCookie('easync_logged', 'true', 2);\n        setCookie('easync_email', json_data_global.email, 2);\n        console.log(response);\n      });\n\n      /*$.ajax({\n        url:'http://easync.com/api/v1.0/User/googleSignIn',\n        type: \"POST\",\n        dataType: \"json\",\n        data: json_data_global,\n        error : function (error) {\n          console.log(\"erdfhfgror\");\n          \n        },\n        succcess: function (response) {\n          console.log(\"successful\");\n          console.log(response);\n        }\n      });*/\n    };\n\n  function setCookie(cname, cvalue, exdays) {\n    var d = new Date();\n    d.setTime(d.getTime() + (exdays*24*60*500));\n    var expires = \"expires=\"+d.toUTCString();\n    document.cookie = cname + \"=\" + cvalue + \"; \" + expires;\n  }</script> <style>h2 {\n		text-align: center;\n	}\n\n	#googlesignin {\n    margin-top: 30px;\n		text-align: center;\n	}\n\n	#my-signin2 > div {\n		width: 250px;\n		margin: 0 auto;\n	}\n  #emLog, #passLog{\n    width:250px;\n    margin: 10px;\n    height:30px;\n  }\n  #btn {\n    width: 250px;\n    margin: 10px;\n    height:30px;\n  }\n\n  input {\n    width: 250px;\n    margin-left: auto;\n    margin-right: auto;\n    height:30px;\n  }\n\n  #loginformscol div {\n    margin-left: auto;\n    margin-right: auto;\n  }\n\n  #loginform {\n    text-align: center;\n  }</style>"),a.put("views/main.html",'<div ng-init="check_cookies()" class="row circleButtons"> <ul class="img-list"> <li class="round-button"> <a ng-href="#/newmeeting"> <span class="glyphicon glyphicon-plus main-icon"></span> <span class="text-content"><p>New Meeting</p></span> </a> </li> <li class="round-button"> <a ng-href="#/scheduled" ng-mouseover=""> <span class="glyphicon glyphicon-calendar main-icon"></span> <span class="text-content"><p>Scheduled</p></span> </a> </li> <li class="round-button"> <a ng-href="#/groups"> <span class="glyphicon glyphicon-glass main-icon"></span><span class="text-content"><p>Groups</p></span> </a> </li> </ul> </div> <script>var $x = $(\'.round-button > a > span.text-content > p\');\n$x.each(function(){\n  var pad = (200 - $(this).height())/2;\n  $(this).css(\'padding-top\',pad);\n});\nvar y = $(\'.round-button > a > span.glyphicon\');\ny.each(function(){\n  var pad2 = (200 - $(this).height())/2;\n  $(this).css(\'padding-top\',pad2);\n});</script> <style>.main-icon {\n  opacity:1;\n  z-index: 9999;\n\n}\n.main-icon:hover {\n   opacity:0\n}\n.main-icon {\n  -webkit-transition: opacity 500ms;\n  -moz-transition: opacity 500ms;\n  -o-transition: opacity 500ms;\n  transition: opacity 500ms;\n   opacity:1;\n  z-index: 9999;\n}</style>'),a.put("views/newmeeting.html",'<div class="container" ng-controller="NewMeetingCtrl as newmeeting"> <h2>Schedule a New Meeting</h2> <div class="row text-center" id="attendees"> <h3> Attendees </h3> <div class="col-md-3" ng-model="attendees"> <p>Currently Selected</p> <p ng-repeat="x in attendees.emails" ng-click="removefromattendees(x)"> {{ x|attendeesValue }} </p> <p ng-repeat="x in attendees.users" ng-click="removefromattendees(x)"> {{ x|attendeesValue }} </p> <p ng-repeat="x in attendees.groups" ng-click="removefromattendees(x)"> {{ x|attendeesValue }} </p> </div> <div class="col-md-3">Near Me</div> <div class="col-md-3" ng-init="loadcontacts()" ng-model="usercontacts"> <p>Add Contact</p> <p ng-repeat="x in usercontacts" ng-click="addcontacttoattendees(x)"> {{ x.name }} </p> </div> <div class="col-md-3" ng-model="usercontacts" ng-init="loadgroups()"> <p>Add Group</p> <p ng-repeat="x in groups" ng-click="addgrouptoattendees(x)"> {{ x.groupname }} </p> </div> </div> <div class="row text-center" id="addattendees"> <button class="btn btn-success" onclick="toggleEmailField()">Add Attendees by Email</button><br> <span id="addbyemailslide"> <input ng-model="attendeeemail" type="email"> <span ng-click="addemailattendee(attendeeemail)" class="glyphicon glyphicon-plus-sign"></span> </span> </div> <div class="row text-center" id="datetimeconstraints"> <form name="datetimeform"> Dates<br> <input name="fromdate" type="date" ng-model="constraints.start_date" v> <input name="todate" type="date" ng-model="constraints.end_date"><br> <span class="error" ng-show="constraints.start_date > constraints.end_date"> End date must be greater or same as start date </span><br> Times<br> <input name="aftertime" type="time" ng-model="constraints.start_time" value="08:30:00" step="600"> <input name="beforetime" type="time" ng-model="constraints.end_time" vvalue="08:30:00" step="600"><br> <span class="error" ng-show="constraints.start_time >= constraints.end_time"> End time must be greater as start time </span><br> Length <select name="duration" ng-model="constraints.duration"> <option value="15"> 15 Minutes</option> <option value="30"> 30 Minutes</option> <option value="45"> 45 Minutes</option> <option value="60"> 1 Hour</option> <option value="120"> 2 Hours</option> </select> <br> <label for="required">Attendance Required </label><input type="checkbox" name="required" id="required" ng-model="constraints.required"> <br> <input type="text" ng-model="constraints.title" placeholder="Meeting Name"> <input type="text" ng-model="constraints.location" placeholder="Meeting Location"><br> <textarea class="form-control" ng-model="constraints.description" placeholder="Meeting Description" id="meeting_description"></textarea> <input type="file" ng-model="constraints.attachment" placeholder="Attachment"> </form> <button class="btn btn-info" ng-show="validateconstraintfields() === false" disabled>Find Possible Meeting Times</button> <button class="btn btn-info" ng-show="validateconstraintfields() === true" ng-click="findmeetingtimes(constraints, attendees)">Find Possible Meeting Times</button> </div> <div class="row text-center" id="possibletimes" ng-show="possibletimes_bool"> <h3> Possible Times</h3> </div> </div> <script>function toggleEmailField() {\n	var inputarea = document.getElementById("addbyemailslide");\n	if ($(inputarea).css(\'display\') === \'none\') \n		$(inputarea).slideDown();\n	else \n		$(inputarea).slideUp();\n}</script> <style>h1 {\n		text-align: center;\n	}\n\n	select {\n		margin-right: 10px;\n	}\n\n	input, select {\n		margin-top: 20px;\n	}\n\n	div div.row {\n		margin-top: 20px;\n		padding-bottom: 15px;\n	}\n\n	#addattendees {\n		margin-top: 0px;\n	}\n\n\n	#addbyemailslide {\n		display: none;\n	}\n	\n	button {\n		margin: auto;\n		display: table;\n		margin-top: 30px;\n	}\n\n	input[type="file"] {\n		margin-right: auto;\n		margin-left: auto;\n		width: 180px;\n	}\n\n	textarea#meeting_description {\n		width: 250px;\n		margin-top: 20px;\n		margin-left: auto;\n		margin-right: auto;\n	}\n	label {\n		padding-right:5px;\n	}</style>'),a.put("views/register.html",'<div class="row loginregrow"> <div class="col-md-8 register-cal"> <time datetime="2014-09-20" class="icon"> <em><span class="glyphicon glyphicon-ok color" aria-hidden="true"></span></em> <strong>Register</strong> </time> </div> <div class="col-md-4"> <div ng-controller="RegisterCtrl as register"> <form name="form" novalidate class="css-form"> <input type="text" ng-model="user.firstname" required placeholder="First Name"> <br> <input type="text" ng-model="user.lastname" required placeholder="Last Name"> <br> <input type="email" ng-model="user.email" name="email" required placeholder="Email"> <br> <div ng-show="form.$submitted"> <div ng-show="form.email.$error.email">Not a valid email!</div> </div> <input type="password" ng-model="user.pass" required id="pass1" name="pass1" placeholder="Password"> <br> <input type="password" name="pass2" ng-model="user.passtwo" pwcheck="pass1" required placeholder="Confirm Password"> <br> <div ng-show="form.pass2.$error"> <div ng-show="form.pass2.$error.pwcheck">Passwords must be identical</div> </div> <input ng-show="form.$valid" type="submit" ng-click="register.register(user)" value="Sign Up" name="submit"> <input ng-show="!form.$valid" type="submit" value="Sign Up" name="submit" disabled> </form> <div id="googlesignin"> <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div> <a onclick="signout_google()">Sign out of google</a> </div> </div> </div> </div> <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script> <script>function signout_google() {\n    	var auth2 = gapi.auth2.getAuthInstance();\n    	auth2.signOut().then(function () {\n      		console.log(\'User signed out.\');\n    	});\n  	}\n\n    function onSignIn(googleUser) {\n      // Useful data for your client-side scripts:\n      var profile = googleUser.getBasicProfile();\n      console.log("ID: " + profile.getId()); // Don\'t send this directly to your server!\n      console.log("Name: " + profile.getName());\n      console.log("Image URL: " + profile.getImageUrl());\n      console.log("Email: " + profile.getEmail());\n\n      // The ID token you need to pass to your backend:\n      var id_token = googleUser.getAuthResponse().id_token;\n      console.log("ID Token: " + id_token);\n\n'+"      console.log(profile.getName().split(' ')[0], profile.getName().split(' ')[1]);\n\n      var data = {\n        'google_ID' : profile.getId(),\n        'firstname' : profile.getName().split(' ')[0],\n        'lastname' : profile.getName().split(' ')[1],\n        'email' : profile.getEmail(),\n        'google_token' : id_token\n      };\n\n      var json_data = JSON.stringify(data);\n\n      $.post('http://easync.com/api/v1.0/User/googleSignIn', function (data) {\n        console.log(data);\n      });\n    };</script> <style>.css-form input[disabled] {\n		color: red;\n	}\n	input {\n		width: 250px;\n		margin:10px;\n		height:30px;\n	}</style>"),a.put("views/scheduled.html",'<div class="row"> <h2>Scheduled Meetings</h2> <table id="scheduledMeetings" ng-init="loadMeetings()"> <tr ng-repeat="x in meetings"> <td>{{x.name}}</td> <td>{{x.location}}</td> <td>{{x.startTime}}</td> <td>{{x.attendees}}</td> </tr> </table> </div> <style></style>'),a.put("views/settings.html",'<div class="userName" ng-init="loadUser()"> <h2> Hello {{name}}</h2> </div> <div class="row loginregrow" ng-controller="SettingsCtrl as settings"> <div class="col-md-6 contactsCol col-centered"> <div class="contactsList" ng-init="loadContacts()"> <table class="table table-hover" id="contactsTable"> <thead> <tr> <th>Contacts</th> </tr> </thead> <tbody> <tr ng-repeat="x in contacts" ng-click="removeContact(x)"> <td>{{ x.name }}</td> </tr> </tbody> </table> </div> <div ng-show="addContact"> <input class="form-control input-sm add-contact" placeholder="email" ng-keypress="handleNewContact($event)" ng-model="newContact"> </div> <div class="alert alert-danger" ng-show="newContactError" role="alert"><strong>Error:</strong> email not registered with Easync</div> <button class="btn" id="addContactButton" ng-click="toggleAddContact()"> Add Contact </button> </div> <!--<div class="col-md-6 addCalCol" ng-init="checkCalendar()">\n		<button class="btn" ng-show="calConnected" disabled> Connect Google Calendar </button>\n		<button class="btn" ng-show="!calConnected" ng-click="connectCal()"> Connect Google Calendar </button>\n		<a href="http://localhost:6969/api/v1.0/User/addGoogleCal"> add calendar </a>\n	</div>--> </div> <style>.name{\n	text-align:left;\n	color:#1B998B;\n}\n\n#contactsTable th {\n	text-align: center;\n}\n\n.contactList > table {\n	margin-right: auto;\n	margin-left: auto;\n	width: 300px;\n	margin-bottom: 40px;\n}\n\ndiv.contactsCol.col-centered {\n	text-align: center;\n}</style>'),a.put("views/techoverview.html",'<div> <h1>Technical Overview</h1> <div> Our team set out to create the perfect group scheduling application: one that could take the schedules of multiple members and produce ideal meeting times for everyone. </div> <h2>Target Audience</h2> <div> Easync is for the busy individual who needs an easier way to collaborate with groups. Professional or personal, it\'s aimed at the kind of person that may be unable to answer the question "When can we meet again?" </div> <h2>Project Requirements</h2> <div>These are some things we felt were absolutely necessary to include in the project.</div> <ul class="techOver"> <li>Google Calendar integration</li> <li>Ideal meeting algorithm</li> <li>Group collaboration</li> </ul> </div> <h2>Technical Implementation</h2> <div class="col-md-4"> <h4>Data Access Layer</h4> <ul class="techOver"> <li>Ubuntu Server</li> <li>MySQL Server</li> </ul> </div> <div class="col-md-4"> <h4>Web Service/REST API</h4> <ul class="techOver"> <li>PHP</li> <li>Apache</li> <li>Ubuntu Server</li> </ul> </div> <div class="col-md-4"> <h4>Presentation Layer</h4> <ul class="techOver"> <li>Angular.js</li> <li>Grunt</li> <li>Bootstrap</li> <li>jQuery</li> </ul> </div> <h2>Database</h2> <div>Here\'s an ER diagram of our database:</div> <img src="/images/ER_Diagram.e0df99ae.png" width="700px" height="400px"> <h2><a href="http://easync.com/public/Documentation/class-Binding.html">API Documentation</a></h2> ');
}]);