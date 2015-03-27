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
                                        deferred.reject(data);
                                    })
                                    .finally(function(){

                                    })
                                ;

                                return deferred.promise;
                            }]
                        }
                    });
    }]);

    dashboard.controller('dashboardCtrl', ['$scope', 'redirect', 'data', '$http', function($scope, redirect, data, $http){
        $scope.user      = data.user;
        $scope.dashboard = data.dashboard;

        angular.element("#inProcessTable").tablesorter();
        angular.element("#requestedTable").tablesorter();
        angular.element("#approvedTable").tablesorter();

        $scope.createListingFlyerRequest = function(e){
            e.preventDefault();
            redirect("/flyer/new");
        }

        angular.element('.queue').click(function(e){
            var target = angular.element(e.target);

            if(target.is('a.cancel')){
                e.preventDefault();

                $http.patch('/queue/cancel/' + target.data('id'), [])
                    .success(function(data){
                        var badge = $('.badge', target.parents('div.panel-default'));
                        badge.html(badge.html() - 1);
                        target.parents('tr').remove();
                    })
                    .error(function(data){
                        console.log(data);
                    })
                    .finally(function(){

                    })
                ;
            }
        });
    }]);
})(settings);