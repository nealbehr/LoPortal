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

    dashboard.controller('dashboardCtrl', ['$scope', 'redirect', '$http', 'data', 'createDraftRequestFlyer', 'waitingScreen', function($scope, redirect, $http, data, createDraftRequestFlyer, waitingScreen){
        $scope.dashboard    = data.dashboard;

        $scope.settingRows  = {};
        $scope.settingRows[settings.queue.state.listingFlyerPending] = {id: 'listingFlyerPending', title: 'Pending', isExpand: false};
        $scope.settingRows[settings.queue.state.requested] = {id: 'requested', title: 'Requested', isExpand: false};
        $scope.settingRows[settings.queue.state.approved]  = {id: 'approved', title: 'Approved', isExpand: false};
        $scope.settingRows[settings.queue.state.declined]  = {id: 'declined', title: 'Declined', isExpand: false};
        $scope.settingRows[settings.queue.state.draft]     = {id: 'draft', title: 'Incomplete', isExpand: false};

        $scope.recalculateExpanded = function(){
            /* expand first not empty */
//            var isExpand = true;
//            for(var i in this.dashboard){
//                this.settingRows[i].isExpand = isExpand && this.dashboard[i].length > 0;
//                isExpand = isExpand && !(this.dashboard[i].length > 0)
//            }

            /* expand all except declined */
            for(var i in this.dashboard){
                this.settingRows[i].isExpand = i != settings.queue.state.declined && this.dashboard[i].length > 0;
            }
        };

        $scope.recalculateExpanded();

        $scope.remove = function(target){
            var requestDraft = (new createDraftRequestFlyer()).fill({id: target.data('id')});

            waitingScreen.show();
            requestDraft.remove()
                .success(function(){
                    $scope.dashboard[target.data('state')].splice([target.data('index')], 1).shift();
                    $scope.recalculateExpanded();
                })
                .finally(function(){
                    waitingScreen.hide();
                })
            ;
        }

        $scope.moveToDeclined = function(target){
            $http.patch('/queue/cancel/' + target.data('id'), [])
                .success(function(data){
                    var element = $scope.dashboard[target.data('state')].splice([target.data('index')], 1).shift();
                    element.state = settings.queue.state.declined;
                    $scope.dashboard[settings.queue.state.declined].push(element);
                    $scope.recalculateExpanded();
                })
                .error(function(data){
                    console.log(data);
                })
            ;
        }

        angular.element('.queue').click(function(e) {
            var target = angular.element(e.target);

            if(target.is('a.cancel')){
                e.preventDefault();

                if(target.data('state') == settings.queue.state.draft){
                    $scope.remove(target);// will removed
                }else{
                    $scope.moveToDeclined(target);//will moved
                }
            } else if (target.is('a.delete')) {
                e.preventDefault();

                $http.delete('/queue/' + target.data('id'), {})
                    .success(function(data) {
                        var element = $scope.dashboard[target.data('state')].splice([target.data('index')], 1).shift();
                        element.state = settings.queue.state.deleted;
                        $scope.recalculateExpanded();
                    })
                    .error(function(data){
                        console.log(data);
                    })
                ;
            }
        });
    }]);
})(settings);