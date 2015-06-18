(function(settings){
    'use strict';
    settings = settings || {};

    var salesDirectorModule = angular.module('salesDirectorModule', ['adminModule']);

    salesDirectorModule.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/admin/salesdirector', {
            templateUrl: '/partials/admin.sales.director.tab',
            controller : 'salesDirectorCtrl',
            access     : { isFree: false }
        }).when('/admin/salesdirector/new', {
            templateUrl: '/partials/admin.sales.director',
            controller :  'salesDirectorCtrl',
            access     : { isFree: false }
        }).when('/admin/salesdirector/:id/edit', {
            templateUrl: '/partials/admin.sales.director',
            controller :  'salesDirectorCtrl',
            access     : { isFree: false }
        });
    }]);

    salesDirectorModule.controller(
        'salesDirectorCtrl',
        ['$scope', 'createAdminRequestFlyer', '$routeParams', 'createProfileUser', 'sessionMessages', '$http',
            function($scope, createAdminRequestFlyer, $routeParams, createProfileUser, sessionMessages, $http)
        {

        }
    ]);

    salesDirectorModule.directive(
        'loAdminSalesDirectorList',
        ['$http', '$location', 'tableHeadCol', 'waitingScreen',  'renderMessage', '$q',
            function($http, $location, tableHeadCol, waitingScreen,  renderMessage, $q)
        {
            return {
                restrict   : 'EA',
                templateUrl: '/partials/admin.sales.director.list',
                link       : function(scope, element, attrs, controllers) {
                    scope.pagination     = {};
                    scope.salesDirectors = [];
                    scope.isLoaded       = false;
                    scope.container      = angular.element('#salesDirectorMessage');
                    scope.searchingString;
                    scope.searchKey;

                    scope.getList = function () {
                        var deferred = $q.defer();

                        waitingScreen.show();
                        $http.get('/admin/salesdirector', {
                            params: $location.search()
                        }).success(function(data) {
                            return deferred.resolve(data);
                        }).finally(function() {
                            waitingScreen.hide();
                        });

                        return deferred.promise;
                    };

                    scope.delete = function(e, key, val) {
                        e.preventDefault();
                        if (!confirm('Are you sure?')) {
                            return false;
                        }

                        waitingScreen.show();

                        var deferred = $q.defer();
                        $http.delete('/admin/salesdirector/'+val.id, {}).success(function(data) {
                            renderMessage('Sales director was deleted.', 'success', scope.container, scope);
                            scope.salesDirectors.splice(key, 1);
                            deferred.resolve(data);
                        }).error(function(data) {
                            deferred.reject(data);
                        }).finally(function() {
                            waitingScreen.hide();
                        });

                        return deferred.promise;
                    };

                    scope.getList().then(function(data) {
                        scope.pagination = data.pagination;
                        scope.salesDirectors = data.salesDirector;
                        scope.searchingString = $location.search()[data.keySearch];
                        scope.searchKey = data.keySearch;

                        function params(settings) {
                            this.key = settings.key;
                            this.title = settings.title;
                        }

                        params.prototype.directionKey = data.keyDirection;
                        params.prototype.sortKey = data.keySort;
                        params.prototype.defaultDirection = data.defDirection;
                        params.prototype.defaultSortKey = data.defField;

                        scope.headParams = [
                            new tableHeadCol(new params({key: 'id', title: 'id', isSortable: true})),
                            new tableHeadCol(new params({key: 'name', title: 'Name', isSortable: true})),
                            new tableHeadCol(new params({key: 'email', title: 'Email', isSortable: true})),
                            new tableHeadCol(new params({key: 'phone', title: 'Phone', isSortable: false})),
                            new tableHeadCol(new params({key: 'created_at', title: 'Created', isSortable: true})),
                            new tableHeadCol(new params({key: 'action', title: 'Actions', isSortable: false}))
                        ];
                    }).finally(function() {
                        scope.isLoaded = true;
                    });
                }
            }
        }
    ]);

    salesDirectorModule.directive(
        'loAdminSalesDirectorForm',
        ['redirect', 'userService', '$http', 'waitingScreen', 'renderMessage', 'getRoles', 'getLenders', '$location',
            '$q', 'sessionMessages', '$anchorScroll', 'loadFile', '$timeout', 'pictureObject',
            function(redirect, userService, $http, waitingScreen, renderMessage, getRoles, getLenders, $location, $q,
                     sessionMessages, $anchorScroll, loadFile, $timeout, pictureObject)
        {
            return {
                restrict   : 'EA',
                templateUrl: '/partials/admin.sales.director.form',
                link       : function(scope, element, attrs, controllers) {


                }
            }
        }
    ]);
})(settings);
