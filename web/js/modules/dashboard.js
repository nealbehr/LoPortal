(function(settings){
    "use strict";
    settings = settings || {};

    var dashboard = angular.module('dashboardModule', ['helperService']);

    dashboard.config(['$routeProvider', function($routeProvider) {
                $routeProvider.
                    when('/', {
                        templateUrl: '/partials/dashboard',
                        controller:  'dashboardCtrl',
                        access: {
                            isFree: false
                        },
                        resolve: {
                            data: ["$q", "$http", 'waitingScreen', function($q, $http, waitingScreen){
                                   var deferred = $q.defer();
                                   waitingScreen.show();
                                   $http.get('/dashboard/')
                                    .success(function(data){
                                        deferred.resolve(data)
                                    })
                                    .error(function(data){
                                        deferred.reject(data);
                                    })
                                    .finally(function(){
                                        waitingScreen.hide();
                                    })
                                ;

                                return deferred.promise;
                            }]
                        }
                    })
                    .when('/dashboard/collateral', {
                        templateUrl: '/partials/dashboard.collateral',
                        controller:  'dashboardCustomCollateral',
                        access: {
                            isFree: false
                        }
                    })
                ;
    }]);

    dashboard.controller('dashboardCustomCollateral', ["$scope", "waitingScreen", "$http", function($scope, waitingScreen, $http){
        $scope.data ={}

        waitingScreen.show();

        $http.get("/dashboard/collateral")
            .success(function(data){
                $scope.data = data;
            })
            .finally(function(){
                waitingScreen.hide();
            })


    }]);

    dashboard.controller('dashboardCtrl', ['$scope', 'redirect', '$http', 'data', function($scope, redirect, $http, data){
        $scope.dashboard    = data.dashboard;

        console.log($scope.dashboard)
        $scope.settingRows  = {}
        $scope.settingRows[settings.queue.stateListingFlyerPending] = {id: 'stateListingFlyerPending', title: 'Pending', isExpand: false};
        $scope.settingRows[settings.queue.stateRequested] = {id: 'requested', title: 'Requested', isExpand: false};
        $scope.settingRows[settings.queue.stateApproved]  = {id: 'approved', title: 'Approved', isExpand: false};
        $scope.settingRows[settings.queue.stateDeclined]  = {id: 'declined', title: 'Declined', isExpand: false};

        var isExpand = true;
        for(var i in $scope.dashboard){
            $scope.settingRows[i].isExpand = isExpand && $scope.dashboard[i].length > 0;
            isExpand = !($scope.dashboard[i].length > 0)
        }

        angular.element('.queue').click(function(e){
            var target = angular.element(e.target);

            if(target.is('a.cancel')){
                e.preventDefault();

                $http.patch('/queue/cancel/' + target.data('id'), [])
                    .success(function(data){
                        var badge = $('.badge', target.parents('div.panel-default'));
                        badge.html(badge.html() - 1);
                        if(badge.html() == 0){
                            badge.parents('.panel-default').remove();
                        }else{
                            target.parents('tr').remove();
                        }

                    })
                    .error(function(data){
                        console.log(data);
                    })
                    .finally(function(){

                    })
                ;
            }
        });
    }]);
})(settings);