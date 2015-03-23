(function(settings){
    "use strict";
    settings = settings || {};

    var dashboard = angular.module('dashboardModule', ['helperService']);

    dashboard.config(['$routeProvider', function($routeProvider) {
                $routeProvider.
                    when('/', {
                        templateUrl: '/partials/dashboard',
                        controller:  'dashboardCtrl',
                        resolve: {user: ["$q", "$http", function($q, $http){
                            var deferred = $q.defer();

                            $http.get('/dashboard/')
                                .success(function(data){
                                    if('user' in data){
                                        deferred.resolve(data.user)
                                    }

                                    console.log(data);
                                })
                                .error(function(data){
                                    //actually you'd want deffered.reject(data) here
                                    //but to show what would happen on success..
                                    deferred.resolve(data);
                                })
                                .finally(function(){

                                })
                            ;

                            return deferred.promise;
                        }]}
                    });
    }]);

    dashboard.controller('dashboardCtrl', ['$scope', '$http', 'redirect', '$cookieStore', 'TOKEN_KEY', function($scope, $http, redirect, $cookieStore, TOKEN_KEY){
        $scope.user = {};

        angular.element("#inProcessTable").tablesorter();
        angular.element("#requestedTable").tablesorter();
        angular.element("#approvedTable").tablesorter();


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