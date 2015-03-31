(function(settings){
    "use strict";
    settings = settings || {};

    var dashboard = angular.module('dashboardModule', ['helperService']);

    dashboard.config(['$routeProvider', function($routeProvider) {
                $routeProvider.
                    when('/', {
                        templateUrl: '/partials/dashboard',
                        controller:  'dashboardCtrl',
                        resolve: {
                            user: ["userService", function(userService){
                                return userService.get();
                            }],
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
                    });
    }]);

    dashboard.controller('dashboardCtrl', ['$scope', 'redirect', '$http', 'user', 'data', function($scope, redirect, $http, user, data){
        $scope.user         = user;
        $scope.dashboard    = data.dashboard;
        $scope.settingRows  = {}
        $scope.queueChecked = true;
        $scope.queueStateApproved = settings.queue.stateApproved;
        $scope.settingRows[settings.queue.stateInProgres] = {id: 'inProcess', title: 'In process', isExpand: false};
        $scope.settingRows[settings.queue.stateRequested] = {id: 'requested', title: 'Requested', isExpand: false};
        $scope.settingRows[settings.queue.stateApproved]  = {id: 'approved', title: 'Approved', isExpand: false};

        var isExpand = true;
        for(var i in $scope.dashboard){
            $scope.settingRows[i].isExpand = isExpand && $scope.dashboard[i].length > 0;
            isExpand = !($scope.dashboard[i].length > 0)
        }

        $scope.show = function(e, tab){
            e.preventDefault();
            this.queueChecked = tab == 'queue';
        }

        $scope.createListingFlyerRequest = function(e){
            e.preventDefault();
            redirect("/flyer/new");
        }

        $scope.createNewApproval = function(e){
            e.preventDefault();
            redirect('/request/approval');
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