(function(settings){
    "use strict";
    settings = settings || {};

    /**
     * Constants
     */
    var PATH = '/dashboard';

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
                    }).when('/dashboard/collateral', {
                        templateUrl: '/partials/dashboard.collateral',
                        controller:  'dashboardCollateralCtrl',
                        access: {
                            isFree: false
                        }
                    })
                ;
    }]);

    dashboard.controller(
        'dashboardCollateralCtrl',
        ['$scope', 'waitingScreen', '$http', '$q',
            function($scope, waitingScreen, $http, $q) {
                $scope.data       = [];
                $scope.categories = [];
                $scope.templates  = [];

                waitingScreen.show();

                var categories = $http.get('/request/template/categories', {cache: true}),
                    templates  = $http.get('/dashboard/templates'),
                    flyer      = $http.get('/dashboard/collateral');
                $q.all([categories, templates, flyer]).then(function(response) {
                    $scope.categories = response[0].data;
                    $scope.templates  = response[1].data;
                    $scope.data       = response[2].data;
                }).finally(function() {
                    waitingScreen.hide();
                });
            }
        ]
    );

    dashboard.controller('dashboardCtrl', ['$scope', 'redirect', '$http', 'data', 'createDraftRequestFlyer', 'waitingScreen', function($scope, redirect, $http, data, createDraftRequestFlyer, waitingScreen){
        $scope.dashboard    = data.dashboard;

        $scope.settingRows  = {};
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
        };

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
        };

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

    dashboard.directive(
        'loDashboardCollateralList',
        ['$http', '$location', 'waitingScreen', 'renderMessage', 'createTemplate',
            function($http, $location, waitingScreen, renderMessage, createTemplate) {
                return {
                    restrict   : 'EA',
                    templateUrl: '/partials/dashboard.template.list',
                    scope      : {
                        categories: '=loCategories',
                        templates : '=loTemplates'
                    },
                    link: function (scope, element, attrs, controllers) {
                        scope.PATH = PATH;
                    }
                }
            }
        ]
    );

    dashboard.directive(
        'dashboardCollateral',
        ['createRequestFlyer', 'createDraftRequestFlyer', 'waitingScreen', '$http',
            function (createRequestFlyer, createDraftRequestFlyer, waitingScreen, $http) {
        return {
            restrict   : 'EA',
            templateUrl: '/partials/dashboard.collateral.row',
            scope      : {
                items: '=loItems'
            },
            link: function (scope, el, attrs, ngModel) {
                scope.categories = [
                    {
                        id   : 0,
                        name : 'Listing Flyers',
                        items: []
                    },
                    {
                        id   : 1,
                        name : 'Archive',
                        items: []
                    }
                ];

                scope.$watch('items', function(newValue){
                    scope.items = newValue;
                    for (var i in scope.items) {
                        if (scope.items[i].archive === '0') {
                            scope.categories[0].items.push(scope.items[i]);
                        }
                        else {
                            scope.categories[1].items.push(scope.items[i]);
                        }
                    }
                });

                scope.archive = function(e, index, queue) {
                    e.preventDefault();

                    waitingScreen.show();

                    $http.get('/request/'+queue.id).success(function(data) {
                        var flaer = (new createDraftRequestFlyer(queue.id)).fill(data);
                        if (queue.archive == '0') {
                            var to   = scope.categories[0].items,
                                from = scope.categories[1].items;
                            queue.archive = flaer.property.archive = '1';
                        }
                        else {
                            var to   = scope.categories[1].items,
                                from = scope.categories[0].items;
                            queue.archive = flaer.property.archive = '0';
                        }

                        flaer.update().finally(function() {
                            (to || []).splice(index, 1);
                            (from || []).push(queue);

                            waitingScreen.hide();
                        });
                    });
                };
            }
        };
    }]);
})(settings);