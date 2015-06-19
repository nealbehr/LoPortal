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
            controller :  'salesDirectorEditCtrl',
            access     : { isFree: false }
        });
    }]);

    salesDirectorModule.factory('getSalesDirectors', ['$q', '$http', function($q, $http) {
        var salesDirectors = [];

        return function(needReload) {
            var deferred = $q.defer();
            if (salesDirectors.length !== 0 && !needReload) {
                return $q.when(salesDirectors);
            }

            $http.get('/admin/salesdirector').success(function(data) {
                salesDirectors = data;
                deferred.resolve(data);
            }).error(function(data) {
                deferred.reject(data);
            });

            return deferred.promise;
        }
    }]);

    salesDirectorModule.service(
        'createSalesDirector',
        ['$q', '$http', 'createSalesDirectorBase', 'getSalesDirectors',
            function($q, $http, createSalesDirectorBase, getSalesDirectors) {
        return function() {
            var salesDirector = new createSalesDirectorBase(),
                path          = '/admin/salesdirector';

            salesDirector.getList = function(needReload) {
                return getSalesDirectors(needReload)
            };

            salesDirector.delete = function() {
                if (!this.id) {
                    alert('User id has not set.');
                }

                var deferred = $q.defer();
                $http.delete(path+'/'+this.id, {}).success(function(data) {
                    deferred.resolve(data);
                }).error(function(data){
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            salesDirector.save = function() {
                return this.id ? this.update() : this.add();
            };

            salesDirector.update = function() {
                var deferred = $q.defer();
                $http.put(path+'/'+this.id, {salesDirector: this.getFields4Save()}).success(function(data){
                    deferred.resolve(data);
                }).error(function(data){
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            salesDirector.add = function() {
                var deferred = $q.defer();
                $http.post(path, {salesDirector: this.getFields4Save()}).success(function(data) {
                    salesDirector.id = data.id;
                    deferred.resolve(data);
                }).error(function(data){
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            return salesDirector;
        }
    }]);

    salesDirectorModule.service('createSalesDirectorBase', ['$q', '$http', function($q, $http) {
        return function() {
            var self   = this;

            this.id    = null;
            this.name  = null;
            this.email = null;
            this.phone = null;

            this.fill = function(data) {
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        this[key] = data[key];
                    }
                }
                return this;
            };

            this.getFields4Save = function() {
                var result = {};
                for (var key in this) {
                    if (this.hasOwnProperty(key)) {
                        if (typeof this[key] == 'function') {
                            continue;
                        }
                        result[key] = this[key];
                    }
                }
                return result;
            };

            this.get = function(id) {
                var deferred = $q.defer();
                $http.get('/admin/salesdirector/'+id).success(function(data) {
                    self.fill(data);
                    deferred.resolve(self)
                }).error(function(data) {
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            this.clear = function() {
                for (var key in this) {
                    if (this.hasOwnProperty(key)) {
                        if (typeof this[key] == 'function') {
                            continue;
                        }
                        this[key] = undefined;
                    }
                }
            };

            this.save = function() {
                throw new Error('Add must be override');
            };

            this.add = function() {
                throw new Error('Request add must be override');
            };

            this.update = function() {
                throw new Error('Request update must be override');
            };
        }
    }]);

    salesDirectorModule.controller(
        'salesDirectorCtrl',
        ['$scope', 'createSalesDirector', function($scope, createSalesDirector) {
            $scope.salesDirector = createSalesDirector();
        }
    ]);

    salesDirectorModule.controller(
        'salesDirectorEditCtrl', ['$scope', 'createSalesDirector', '$routeParams',
            function($scope, createSalesDirector, $routeParams)
        {
            createSalesDirector().get($routeParams.id).then(function(data) {
                $scope.salesDirector = data;
            });
        }
    ]);

    salesDirectorModule.directive(
        'loAdminSalesDirectorList',
        ['$http', '$location', 'tableHeadCol', 'waitingScreen', 'renderMessage', 'createSalesDirector',
            function($http, $location, tableHeadCol, waitingScreen, renderMessage, createSalesDirector)
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

                    scope.delete = function(e, key, val) {
                        e.preventDefault();
                        if (!confirm('Are you sure?')) {
                            return false;
                        }

                        waitingScreen.show();
                        var salesDirector = createSalesDirector();
                        salesDirector.id = val.id;
                        salesDirector.delete().then(function() {
                            renderMessage('Sales director was deleted.', 'success', scope.container, scope);
                            scope.salesDirectors.splice(key, 1);
                        }).catch(function(data) {
                            renderMessage(data.message, 'danger', scope.container, scope);
                        }).finally(function() {
                            waitingScreen.hide();
                        });

                        salesDirector = null;
                    };

                    waitingScreen.show();

                    $http.get('admin/salesdirector', {params: $location.search()}).success(function(data) {
                        for (var i in data.salesDirectors) {
                            scope.salesDirectors.push(createSalesDirector().fill(data.salesDirectors[i]));
                        }

                        scope.pagination      = data.pagination;
                        scope.searchingString = $location.search()[data.keySearch];
                        scope.searchKey       = data.keySearch;

                        function params(settings) {
                            this.key = settings.key;
                            this.title = settings.title;
                        }

                        params.prototype.directionKey     = data.keyDirection;
                        params.prototype.sortKey          = data.keySort;
                        params.prototype.defaultDirection = data.defDirection;
                        params.prototype.defaultSortKey   = data.defField;

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
                        waitingScreen.hide();
                    });
                }
            }
        }
    ]);

    salesDirectorModule.directive(
        'loAdminSalesDirectorForm',
        ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll',
            function(waitingScreen, renderMessage, sessionMessages, $anchorScroll)
        {
            return {
                restrict   : 'EA',
                templateUrl: '/partials/admin.sales.director.form',
                scope      : { salesDirector: '=loSalesDirector' },
                link       : function(scope, element, attrs, controllers) {
                    scope.container  = angular.element('#salesDirectorMessage');
                    scope.hideErrors = true;

                    scope.$watch('salesDirector.id', function(newVal, oldVal) {
                        scope.title = newVal? 'Edit Sales Director': 'Add Sales Director';
                    });

                    scope.isValidEmail = function(form) {
                        if (!form.email) {
                            return;
                        }

                        return (form.$submitted || form.email.$touched)
                            && (form.email.$error.email || form.email.$error.required);
                    };

                    scope.showErrors = function(e) {
                        e.preventDefault();
                        this.hideErrors = true;
                    };

                    scope.gotoErrorMessage = function() {
                        $anchorScroll(scope.container.attr('id'));
                    };

                    scope.submit = function(formSalesDirector, $event) {
                        if (!formSalesDirector.$valid) {
                            this.hideErrors = false;
                            this.gotoErrorMessage();
                            return false;
                        }
                        this.save();
                    };

                    scope.save = function() {
                        waitingScreen.show();

                        scope.salesDirector.save().then(function(data) {
                            sessionMessages.addSuccess('Successfully saved.');
                            history.back();
                        }).catch(function(data) {
                            var errors = '';
                            if ('message' in data) {
                                errors += data.message+' ';
                            }

                            if ('form_errors' in data){ 
                                errors += data.form_errors.join(' ');
                            }

                            renderMessage(errors, 'danger', scope.container, scope);
                            scope.gotoErrorMessage();
                        }).finally(function() {
                            waitingScreen.hide();
                        });
                    };

                    scope.cancel = function(e) {
                        e.preventDefault();
                        history.back();
                    };

                    scope.delete = function(e) {
                        e.preventDefault();
                        if (!confirm('Are you sure?')) {
                            return false;
                        }

                        waitingScreen.show();
                        scope.salesDirector.delete().then(function() {
                            sessionMessages.addSuccess('Sales director was deleted.');
                            scope.salesDirector.clear();
                            history.back();
                        }).catch(function(data) {
                            renderMessage(data.message, 'danger', scope.container, scope);
                        }).finally(function() {
                            waitingScreen.hide();
                        });
                    };
                }
            }
        }
    ]);
})(settings);
