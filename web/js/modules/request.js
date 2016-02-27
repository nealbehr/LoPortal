(function(){
    "use strict";

    var request = angular.module('requestModule', ['helperService']);

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
            button  : 'Submit',
            header  : 'New Listing Flyer',
            infoText: 'Listing Flyers are a great way to show how REX HomeBuyer can provide half the down payment for '
                +'a particular home. Use this tool to not only get a pre-approval but a cobranded flyer with your Real '
                +'Estate Agent.'
        };

        $scope.realtor = {};

        $scope.$on('requestFlyerSaved', function() {
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
        ['redirect', '$scope', 'createPropertyApproval',
        function(redirect, $scope, createPropertyApproval)
    {
        $scope.request = createPropertyApproval();
        $scope.lat     = 37.7749295;
        $scope.lng     = -122.41941550000001;
        $scope.titles  = {
            button  : 'Submit',
            header  : 'New Property Prequalification',
            infoText: 'Have a property that needs to be prequalified? Simply type in the address. Don’t forget to '
                +'indicate if you have a buyer for a quick and easy follow up!'
        };

        $scope.$on('propertyApprovalSaved', function(event, data) {
            if (typeof data === 'object' && data.hasOwnProperty('id')) {
                redirect('/request/success/property/'+data.id);
            }
        });
    }]);

    request.controller(
        'RequestSuccessController',
        ['redirect', '$scope', '$routeParams', '$http', '$timeout', 'progressBar', '$rootScope',
        function(redirect, $scope, $routeParams, $http, $timeout, progressBar, $rootScope)
    {
        $scope.statusId   = null;
        $scope.queueId    = null;
        $scope.processing = false;
        $scope.request    = getRequestByType();

        var interval      = 1000,
            endTime       = 10000,
            timeCounter   = 0,
            text          = '';

        if ($routeParams.id) {
            $scope.queueId = $routeParams.id;
            progressBar.setText(text).show();
            handleQueue();
        }
        else {
            $scope.processing = true;
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
                $scope.processing = true;
                progressBar.setProgress(100).hide();

                // Mixpanel analytics
                if ($rootScope.currentUser) {
                    mixpanel.identify($rootScope.currentUser.id);
                    if ($scope.statusId == 1 || $scope.statusId == 2 || $scope.statusId == 3) {
                        mixpanel.track('Approval page is viewed');
                    }
                    else {
                        mixpanel.track('Additional due diligence page is viewed');
                    }
                }
            }
        }

        function getRequestByType()
        {
            return $routeParams.type === 'property'
                ? new RequestBase('Request Property Approval', '')
                : new RequestBase('Request Another Flyer', 'flyer/new');
        }

        function RequestBase(title, url)
        {
            this.title = title;
            this.url   = url;
        }
    }]);
})();