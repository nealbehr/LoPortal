(function(){
    "use strict";

    var request = angular.module('requestModule', ['helperService']);

    request.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/flyer/new', {
                templateUrl: '/partials/request.flyer.new',
                controller:  'RequestController',
                access: {
                    isFree: false
                }
            })
            .when('/request/success/:type',{
                templateUrl: '/partials/request.success',
                controller:  'RequestSuccessController',
                access: {
                    isFree: false
                }
            })
            .when('/request-success/:type/:id',{
                templateUrl: '/partials/request.success',
                controller:  'RequestSuccessController',
                access: {
                    isFree: false
                }
            })
            .when('/', {
                templateUrl: '/partials/request.property.approval',
                controller:  'RequestPropertyApprovalController',
                access: {
                    isFree: false
                }
            })
            .when('/flyer/:id/edit', {
                templateUrl: '/partials/request.flyer.edit',
                controller:  'RequestFlyerEditController',
                access: {
                    isFree: false
                }
            })
            .when('/flyer/from/approval/:id/edit', {
                templateUrl: '/partials/request.flyer.edit',
                controller:  'RequestFromApprovalController',
                access: {
                    isFree: false
                }
            })
        ;
    }]);

    request.controller('RequestFromApprovalController', ['$scope', 'createFromPropertyApproval', '$routeParams', "createProfileUser", 'sessionMessages', '$http', function($scope, createFromPropertyApproval, $routeParams, createProfileUser, sessionMessages, $http){
        $scope.request = {};
        $scope.realtor = {};
        $scope.titles = {
            button: "Submit",
            header: "Create Listing Flyer Request from property approval"
        };

        $scope.$on('requestFlyerSaved', function () {
            sessionMessages.addSuccess("Successfully saved.");
            history.back();
        });

        $http.get('/request/approval/' + $routeParams.id)
            .success(function(info){
                $scope.request = (new createFromPropertyApproval($routeParams.id)).fill(info);
                $scope.realtor = createProfileUser().fill(info.user);
                $scope.user = $scope.realtor;
            })
        ;
    }]);

    request.controller('RequestFlyerEditController', ['$scope', 'createRequestFlyer', '$routeParams', "createProfileUser", 'sessionMessages', '$http', function($scope, createRequestFlyer, $routeParams, createProfileUser, sessionMessages, $http){
        $scope.request = {};
        $scope.realtor = {};
        $scope.titles = {
            button: "Submit",
            header: "Edit Listing Flyer Request"
        };

        $scope.$on('requestFlyerSaved', function () {
            sessionMessages.addSuccess("Successfully saved.");
            history.back();
        });

        $http.get('/request/' + $routeParams.id)
            .success(function(info){
                $scope.request = (new createRequestFlyer($routeParams.id)).fill(info);
                $scope.realtor = createProfileUser().fill(info.user);
                $scope.user = $scope.realtor;
            })
        ;
    }]);

    request.controller('RequestController', ['$scope', 'redirect', '$http', '$q', '$timeout', 'getInfoFromGeocoder', 'waitingScreen', 'parseGoogleAddressComponents', "userService", "createRequestFlyer", function($scope, redirect, $http, $q, $timeout, getInfoFromGeocoder, waitingScreen, parseGoogleAddressComponents, userService, createRequestFlyer){
        $scope.titles = {
            button: "Submit",
            header: "Listing Flyer Request Form"
        };

        $scope.realtor = {};

        $scope.$on('requestFlyerSaved', function () {
            redirect('/request/success/flyer');
        });

        userService
            .get()
            .then(function(user){
                $scope.realtor = user;
                $scope.user = user;
            })
        ;

        $scope.request = createRequestFlyer();
    }]);

    request.controller(
        'RequestPropertyApprovalController',
        ['redirect', '$scope', 'createPropertyApproval', 'userService',
        function(redirect, $scope, createPropertyApproval, userService)
    {
        $scope.request = createPropertyApproval();
        $scope.lat     = 37.7749295;
        $scope.lng     = -122.41941550000001;
        $scope.titles  = {
            button: "Submit",
            header: "Property Prequalification Request Form"
        };

        userService.get().then(function(user) {
            $scope.$on('propertyApprovalSaved', function(event, data) {
                if (typeof data === 'object' && data.hasOwnProperty('id')) {
                    redirect('/request-success/approval/'+data.id);
                }
                else {
                    redirect('/request/success/approval');
                }
            });
        });
    }]);

    request.controller(
        'RequestSuccessController',
        ['redirect', '$scope', '$routeParams', '$http', '$timeout', 'progressBar',
        function(redirect, $scope, $routeParams, $http, $timeout, progressBar)
    {
        $scope.statusId      = null;
        $scope.endProcessing = false;
        $scope.request       = getRequestByType($routeParams.type);

        var interval         = 1000,
            endTime          = 10000,
            timeCounter      = 0,
            text             = '';

        if ($routeParams.id) {
            progressBar.setText(text).show();
            handleQueue();
        }

        function handleQueue() {
            $http.get('/queue/'+$routeParams.id).success(function(data) {
                $scope.statusId = data.status_id;
            });

            // Starts with "identifying property details" holds for 1 seconds
            if (timeCounter < 1000) {
                text = 'identifying property details';
            }
            // Then changes to "generating demand metrics" holds for 2 seconds
            else if (timeCounter < 3000) {
                text = 'generating demand metrics';
            }
            // Then changes to "evaluating supply metrics" holds for 2 seconds
            else if (timeCounter < 5000) {
                text = 'evaluating supply metrics';
            }
            // Then changes to "developing projections" holds for the remaining 3 seconds or until a response is
            else if (timeCounter < 7000) {
                text = 'developing projections';
            }

            progressBar.setProgress(Math.round(100/(endTime/timeCounter))).setText(text);

            if ($.isNumeric($scope.statusId) === false && ((timeCounter += interval) < endTime)) {
                $timeout(handleQueue, interval);
            }
            else {
                $scope.endProcessing = true;
                progressBar.setProgress(100).hide();
            }
        }

        function getRequestByType(type) {
            return type == 'approval'
                        ? new RequestBase('Request property approval', '/')
                        : new RequestBase('Request Another Flyer', 'flyer/new')
            ;
        }

        function RequestBase(title, url) {
            this.title = title;
            this.url   = url;
        }
    }]);
})();