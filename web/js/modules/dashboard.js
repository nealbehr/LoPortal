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
        $scope.user = {};
        $http.get('/dashboard')
             .success(function(data){
                if('user' in data){
                    $scope.user = data.user;
                }

                console.log(data);
            })
            .finally(function(){

            })
        ;

        $scope.logout = function(e){
            e.preventDefault();
            $http.delete('/logout')
                .success(function(data){

                })
                .finally(function(){

                })
            ;

            return false;
        }
    }]);
})(settings);