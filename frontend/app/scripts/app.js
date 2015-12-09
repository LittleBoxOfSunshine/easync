'use strict';

/**
 * @ngdoc overview
 * @name easyncApp
 * @description
 * # easyncApp
 *
 * Main module of the application.
 */


angular
  .module('easyncApp', [
    'ngAnimate',
    'ngCookies',
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngTouch',
    'ui.calendar'
  ])
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
        controller: 'MainCtrl',
        controllerAs: 'main'
      })
      .when('/register', {
        templateUrl: 'views/register.html',
        controller: 'RegisterCtrl',
        controllerAs: 'register'
      })
      .when('/login', {
        templateUrl: 'views/login.html',
        controller: 'LoginCtrl',
        controllerAs: 'login'
      })
      .when('/calendar', {
        templateUrl: 'views/calendar.html',
        controller: 'CalendarCtrl',
        controllerAs: 'calendar'
      })
      .when('/scheduled', {
        templateUrl: 'views/scheduled.html',
        controller: 'ScheduledCtrl',
        controllerAs: 'scheduled'
      })
      .when('/home', {
        templateUrl: 'views/home.html',
        controller: 'HomeCtrl',
        controllerAs: 'home'
      })
      .when('/newmeeting', {
        templateUrl: 'views/newmeeting.html',
        controller: 'NewMeetingCtrl',
        controllerAs: 'newmeeting'
      })
      .when('/groups', {
        templateUrl: 'views/groups.html',
        controller: 'GroupsCtrl',
        controllerAs: 'groups'
      })
      .when('/about',{
        templateUrl: 'views/about.html',
        controller: 'AboutCtrl',
        controllerAs: 'about'
      })
      .when('/gbwindow',{
        templateUrl: 'views/main.html',
        controller: 'GlobalWindowCtrl',
        controllerAs: 'windowcontrol'
      })
      .when('/techoverview',{
        templateUrl: 'views/techoverview.html',
        controller: 'TechOverviewCtrl',
        controllerAs: 'techoverview'
      })
      .when('/devprocess',{
        templateUrl: 'views/devprocess.html',
        controller: 'DevProcessCtrl',
        controllerAs: 'devprocess'
      })
      .otherwise({
        redirectTo: '/'
      });
  })
  .factory('LoggedInService', function($cookies, $location) {
      return {
        loggedIn: function() {
          if ($cookies.get('easync_logged')) {
            return true;
          } else {
            return false;
          }
        },
        set_or_refresh_cookie: function() {
          var expire_date = new Date((new Date()).valueOf() + 1000*36);
          $cookies.put('easync_logged', 'true', {'expires': expire_date});
          return true;
        },
        logout: function() {
          if(this.loggedIn()) {
            $cookies.remove('easync_logged');
            $cookies.remove('slim_session');
          } else {
            console.log("tried to log out, but wasn't logged in");
          }
          $location.path('login');
          return true;
        }

      };
      
  }).factory('GlobalIPService', function() {
    return {
      //ip: "http://52.27.123.122/"
      ip: "http://localhost:6969/"
    };

  }).config(function ($httpProvider) {
  $httpProvider.defaults.headers.common = {};
  $httpProvider.defaults.headers.post = {};
  $httpProvider.defaults.headers.put = {};
  $httpProvider.defaults.headers.patch = {};
}); // http://stackoverflow.com/questions/33660712/angularjs-post-fails-response-for-preflight-has-invalid-http-status-code-404
