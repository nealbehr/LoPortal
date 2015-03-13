(function(settings){
    "use strict";
    settings = settings || {};

    var dashboard = angular.module('dashboardModule', ['helperService']);

    dashboard.config(['$routeProvider', function($routeProvider) {
                $routeProvider.
                    when('/', {
                        templateUrl: '/partials/dashboard',
                        controller:  'dashboardCtrl'
                    });
    }]);

    dashboard.controller('dashboardCtrl', ['$scope', '$http', function($scope, $http){
        $http.get('/dashboard')
             .then(function(data){
//                console.log(data);
            })
            .finally(function(){

            });
        ;
    }]);
})(settings);