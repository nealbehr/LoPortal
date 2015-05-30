(function(settings){
    "use strict";

    var user = angular.module('userProfileModule', []);

    user.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/user/:id/edit', {
                templateUrl: '/partials/profile',
                controller:  'userProfileCtrl',
                resolve: {
                    officerData: ["$q", "$http", '$route', 'waitingScreen', 'userService', 'createProfileUser', function($q, $http, $route, waitingScreen, userService, createProfileUser) {
                        return userService.get()
                    }]
                },
                access: {
                    isFree: false
                }
            })
    }]);

    user.controller('userProfileCtrl', ['$scope', '$route', 'createProfileUser', '$routeParams', 'userService', "$q", "officerData", function($scope, $route, createProfileUser, $routeParams, userService, $q, officerData) {
        $scope.officer = officerData;

//        userService
//            .get()
//            .then(function(user){
//                if(user.id == $routeParams.id){
//                    return $q.when(angular.copy(user));
//                }
//
//                return createProfileUser().get($routeParams.id);
//            })
//            .then(function(user){
//                $scope.officer = user;
//            })
    }]);


})();