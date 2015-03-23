(function(settings){
    "use strict";
    settings = settings || {};

    var dashboard = angular.module('dashboardModule', ['helperService']);

    dashboard.config(['$routeProvider', function($routeProvider) {
                $routeProvider.
                    when('/', {
                        templateUrl: '/partials/dashboard',
                        controller:  'dashboardCtrl',
                        resolve: {
                            data: ["$q", "$http", function($q, $http){
                                var deferred = $q.defer();

                                $http.get('/dashboard/')
                                    .success(function(data){
                                        deferred.resolve(data)
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
                            }]
                        }
                    });
    }]);

    dashboard.controller('dashboardCtrl', ['$scope', 'redirect', 'data', function($scope, redirect, data){
        $scope.user = data.user;

        angular.element("#inProcessTable").tablesorter();
        angular.element("#requestedTable").tablesorter();
        angular.element("#approvedTable").tablesorter();

        $scope.createListingFlyerRequest = function(e){
            e.preventDefault();
            redirect("/flyer/new");
        }
    }]);
})(settings);