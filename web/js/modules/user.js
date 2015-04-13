(function(settings){
    "use strict";
    settings = settings || {};

    var user = angular.module('userProfileModule', []);

    user.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/user/:id/edit', {
                templateUrl: '/partials/profile',
                controller:  'userProfileCtrl',
                access: {
                    isFree: false
                }
            })
    }]);

    user.controller('userProfileCtrl', ['$scope', 'createProfileUser', '$routeParams', 'userService', "$q", "$location", function($scope, createProfileUser, $routeParams, userService, $q, $location){
        $scope.officer = {};

        userService
            .get()
            .then(function(user){
                if(user.id == $routeParams.id){
                    return $q.when(angular.copy(user));
                }

                return createProfileUser().get($routeParams.id);
            })
            .then(function(user){
                $scope.officer = user;
            })
    }]);


})(settings);