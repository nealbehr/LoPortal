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

    user.controller('userProfileCtrl', ['$scope', 'createProfileUser', '$routeParams', function($scope, createProfileUser, $routeParams){
        $scope.officer = {};
        createProfileUser().get($routeParams.id)
            .then(function(user){
                $scope.officer = user;
            })
        ;

        $scope.redirectUrl = '/';
    }]);


})(settings);