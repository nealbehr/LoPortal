(function(settings){
    "use strict";

    var user = angular.module('userProfileModule', []);

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