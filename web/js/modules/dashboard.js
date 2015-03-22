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

    dashboard.controller('dashboardCtrl', ['$scope', '$http', 'redirect', '$cookieStore', 'TOKEN_KEY', function($scope, $http, redirect, $cookieStore, TOKEN_KEY){
        $scope.user = {};

        angular.element("#inProcessTable").tablesorter();
        angular.element("#requestedTable").tablesorter();
        angular.element("#approvedTable").tablesorter();

        $http.get('/dashboard/')
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
                    $cookieStore.remove(TOKEN_KEY)
                    redirect('/login');
                })
                .finally(function(){

                })
            ;

            return false;
        }
    }]);
})(settings);