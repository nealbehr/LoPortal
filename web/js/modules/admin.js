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
            .when('/admin/user/:id/edit', {
                templateUrl: '/partials/admin.panel.user',
                controller:  'adminUserEditCtrl',
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

        $scope.messageContainer = angular.element("#adminMessage");
    }]);

    admin.controller('adminUserEditCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'user', 'createUser', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, user, createUser, $routeParams){
        $scope.user    = user;
        createUser().get($routeParams.id)
            .then(function(user){
                $scope.officer = user;
        });

        $scope.messageContainer = angular.element("#adminMessage");
    }]);

    admin.controller('adminCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'user', function($scope, $http, redirect, $compile, waitingScreen, user){
        $scope.user = user;
    }]);

    admin.directive('loAdminNavBar', [function(){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.nav.bar',
            link: function(scope, element, attrs, controllers){

            }
        }
    }]);

    admin.directive('loAdminUsers', ['$http', function($http){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.users',
            link: function(scope, element, attrs, controllers){
                scope.pagination = {};
                scope.users = [];
                $http.get('/admin/user').success(function(data){
                    scope.pagination = data.pagination;
                    scope.users      = data.users;
                });
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