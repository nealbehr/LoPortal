(function(settings){
    "use strict";
    settings = settings || {};

    var admin = angular.module('adminModule', []);

    admin.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/admin/user/new', {
                templateUrl: '/partials/admin.panel.user',
                controller:  'adminUserNewCtrl',
                resolve: admin.resolve(),
                access: {
                    isFree: false
                }
            })
            .when('/admin', {
                templateUrl: '/partials/admin',
                controller:  'adminCtrl',
                resolve: admin.resolve(),
                access: {
                    isFree: false
                }
            });
    }]);

    admin.controller('adminUserNewCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'user', 'createUser', function($scope, $http, redirect, $compile, waitingScreen, user, createUser){
        $scope.user    = user;
        $scope.officer = createUser();

        $scope.roles   = [];
        $scope.messageContainer = angular.element("#adminMessage");

        $http.get('/admin/roles')
            .success(function(data){
                $scope.roles = [];
                for(var i in data){
                    $scope.roles.push({'title': i, key: data[i]});
                }
            })
            .error(function(data){
                console.log(data);
            });

    }]);

    admin.controller('adminCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'user', function($scope, $http, redirect, $compile, waitingScreen, user){
        $scope.user         = user;
    }]);

    admin.directive('loAdminNavBar', [function(){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.nav.bar',
            link: function(scope, element, attrs, controllers){

            }
        }
    }]);

    admin.resolve = function(){
        return {
            user: ["userService", function(userService){
                return userService.get();
            }]
        }
    }
})(settings);