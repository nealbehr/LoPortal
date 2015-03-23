(function(settings){
    "use strict";
    settings = settings || {};

    var request = angular.module('requestModule', ['helperService']);

    request.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/flyer/new', {
                templateUrl: '/partials/request.flyer.new',
                controller:  'requestCtrl',
                resolve: {
                    data: ["$q", "$http", function($q, $http){
                        var deferred = $q.defer();

                        $http.get('/user/me')
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

    request.controller('requestCtrl', ['$scope', 'redirect', 'data', function($scope, redirect, data){
        $scope.user = data;


    }]);
})(settings);