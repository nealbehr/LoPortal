(function(settings){
    "use strict";
    settings = settings || {};

    var request = angular.module('requestModule', ['helperService']);

    request.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/flyer/new', {
                templateUrl: '/partials/request.flyer.new',
                controller:  'requestCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/request/success/:type',{
                templateUrl: '/partials/request.success',
                controller:  'requestSuccessCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/request/approval', {
                templateUrl: '/partials/request.property.approval',
                controller:  'requestPropertyApprovalCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/flyer/:id/edit', {
                templateUrl: '/partials/request.flyer.edit',
                controller:  'requestFlyerEditCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/flyer/from/approval/:id/edit', {
                templateUrl: '/partials/request.flyer.edit',
                controller:  'requestFromApprovalCtrl',
                access: {
                    isFree: false
                }
            })
        ;
    }]);

    request.controller('requestFromApprovalCtrl', ['$scope', 'createFromPropertyApproval', '$routeParams', "createProfileUser", 'sessionMessages', '$http', function($scope, createFromPropertyApproval, $routeParams, createProfileUser, sessionMessages, $http){
        $scope.request = {};
        $scope.realtor = {};
        $scope.titles = {
            button: "Submit",
            header: "Create Listing Flyer Request from property approval"
        }

        $scope.$on('requestFlyerSaved', function () {
            sessionMessages.addSuccess("Successfully saved.");
            history.back();
        });

        $http.get('/request/approval/' + $routeParams.id)
            .success(function(info){
                $scope.request = (new createFromPropertyApproval($routeParams.id)).fill(info);
                $scope.realtor = createProfileUser().fill(info.user);
            })
        ;
    }]);

    request.controller('requestFlyerEditCtrl', ['$scope', 'createRequestFlyer', '$routeParams', "createProfileUser", 'sessionMessages', '$http', function($scope, createRequestFlyer, $routeParams, createProfileUser, sessionMessages, $http){
        $scope.request = {};
        $scope.realtor = {};
        $scope.titles = {
            button: "Submit",
            header: "Edit Listing Flyer Request"
        }

        $scope.$on('requestFlyerSaved', function () {
            sessionMessages.addSuccess("Successfully saved.");
            history.back();
        });

        $http.get('/request/' + $routeParams.id)
            .success(function(info){
                $scope.request = (new createRequestFlyer($routeParams.id)).fill(info);
                $scope.realtor = createProfileUser().fill(info.user);
            })
        ;
    }]);

    request.controller('requestCtrl', ['$scope', 'redirect', '$http', '$q', '$timeout', 'getInfoFromGeocoder', 'waitingScreen', 'parseGoogleAddressComponents', "userService", "createRequestFlyer", function($scope, redirect, $http, $q, $timeout, getInfoFromGeocoder, waitingScreen, parseGoogleAddressComponents, userService, createRequestFlyer){
        $scope.titles = {
            button: "Submit",
            header: "Listing Flyer Request Form"
        }

        $scope.realtor = {};

        $scope.$on('requestFlyerSaved', function () {
            redirect('/request/success/flyer');
        });

        userService
            .get()
            .then(function(user){
                $scope.realtor = user;
            })
        ;

        $scope.request = createRequestFlyer()
    }]);

    request.controller('requestPropertyApprovalCtrl', ['redirect', '$scope', "createPropertyApproval", function(redirect, $scope, createPropertyApproval){
        $scope.request = createPropertyApproval();
        $scope.lat = 37.7749295;
        $scope.lng = -122.41941550000001;
        $scope.titles = {
            button: "Submit",
            header: "Property Approval Request Form"
        }

        $scope.$on('propertyApprovalSaved', function () {
            redirect('/request/success/approval');
        });
    }]);

    request.controller('requestSuccessCtrl', ['redirect', '$scope', '$routeParams', function(redirect, $scope, $routeParams){
        $scope.request = getRequestByType($routeParams.type);

        function getRequestByType(type){
            return type == 'approval'
                        ? new requestBase('Request property approval', 'request/approval')
                        : new requestBase('Request Another Flyer', 'flyer/new')
            ;
        }
        function requestBase(title, url){
            this.title = title;
            this.url   = url;
        }
    }]);

})(settings);