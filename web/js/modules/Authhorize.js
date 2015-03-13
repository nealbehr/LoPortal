(function(settings){
    "use strict";
    settings = settings || {};

    var authorize = angular.module('authModule', []);

    authorize.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/login', {
                templateUrl: '/partials/login',
                controller:  'authorizeCtrl'
            });
    }]);

    authorize.controller('authorizeCtrl', ['$scope', '$http', function($scope, $http){
//        $http.get('/login')
//            .then(function(data){
//
//            })
//            .finally(function(){
//
//            });
//        ;
    }]);
})(settings);