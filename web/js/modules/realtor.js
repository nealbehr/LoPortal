(function(settings) {
    'use strict';
    settings = settings || {};

    var PATH = '/admin/realtor';

    var realtorModule = angular.module('realtorModule', ['adminModule']);

    realtorModule.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when(PATH, {
            templateUrl: '/partials/admin.realtor.tab',
            controller : 'realtorCtrl',
            access     : { isFree: false }
        }).when(PATH+'/new', {
            templateUrl: '/partials/admin.realtor',
            controller :  'realtorCtrl',
            access     : { isFree: false }
        }).when(PATH+'/:id/edit', {
            templateUrl: '/partials/admin.realtor',
            controller :  'realtorEditCtrl',
            access     : { isFree: false }
        });
    }]);

    realtorModule.factory('getRealtor', ['$q', '$http', function($q, $http) {
        var realtors = [];

        return function(needReload) {
            var deferred = $q.defer();
            if (realtors.length !== 0 && !needReload) {
                return $q.when(realtors);
            }

            $http.get(PATH).success(function(data) {
                realtors = data;
                deferred.resolve(data);
            }).error(function(data) {
                deferred.reject(data);
            });

            return deferred.promise;
        }
    }]);

    realtorModule.service(
        'createRealtor',
        ['$q', '$http', 'createRealtorBase', 'getRealtor',
            function($q, $http, createRealtorBase, getRealtor) {
                return function() {
                    var realtor = new createRealtorBase();

                    realtor.getList = function(needReload) {
                        return getRealtor(needReload)
                    };

                    realtor.delete = function() {
                        if (!this.id) {
                            alert('User id has not set.');
                        }

                        var deferred = $q.defer();
                        $http.delete(PATH+'/'+this.id, {}).success(function(data) {
                            deferred.resolve(data);
                        }).error(function(data){
                            deferred.reject(data);
                        });

                        return deferred.promise;
                    };

                    realtor.save = function() {
                        return this.id ? this.update() : this.add();
                    };

                    realtor.update = function() {
                        var deferred = $q.defer();
                        $http.put(PATH+'/'+this.id, {realtor: this.getFields4Save()}).success(function(data){
                            deferred.resolve(data);
                        }).error(function(data){
                            deferred.reject(data);
                        });

                        return deferred.promise;
                    };

                    realtor.add = function() {
                        var deferred = $q.defer();
                        $http.post(PATH, {realtor: this.getFields4Save()}).success(function(data) {
                            realtor.id = data.id;
                            deferred.resolve(data);
                        }).error(function(data){
                            deferred.reject(data);
                        });

                        return deferred.promise;
                    };

                    return realtor;
                }
            }]);

    realtorModule.service('createRealtorBase', ['$q', '$http', function($q, $http) {
        return function() {
            var self = this;

            this.id                = null;
            this.realty_company_id = null;
            this.first_name        = null;
            this.last_name         = null;
            this.bre_number        = null;
            this.email             = null;
            this.phone             = null;
            this.photo             = null;

            this.getPicture = function() {
                return this.photo;
            };

            this.setPicture = function(param) {
                this.photo = param;

                return this;
            };

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
                $http.get(PATH+'/'+id).success(function(data) {
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

    realtorModule.controller('realtorCtrl', ['$scope', 'createRealtor', function($scope, createRealtor) {
        $scope.realtor = createRealtor();
        $scope.PATH    = PATH;
    }]);

    realtorModule.controller(
        'realtorEditCtrl',
        ['$scope', 'createRealtor', '$routeParams',
            function($scope, createRealtor, $routeParams)
        {
            createRealtor().get($routeParams.id).then(function(data) {
                $scope.realtor = data;
            });
            $scope.PATH    = PATH;
        }
    ]);

    realtorModule.directive(
        'loAdminRealtorList',
        ['$http', '$location', 'tableHeadCol', 'waitingScreen', 'renderMessage', 'createRealtor',
            function($http, $location, tableHeadCol, waitingScreen, renderMessage, createRealtor) {
                return {
                    restrict   : 'EA',
                    templateUrl: '/partials/admin.realtor.list',
                    link       : function(scope, element, attrs, controllers) {
                        scope.pagination = {};
                        scope.realtors   = [];
                        scope.isLoaded   = false;
                        scope.container  = angular.element('#realtorMessage');
                        scope.searchingString;
                        scope.searchKey;

                        scope.delete = function(e, key, val) {
                            e.preventDefault();
                            if (!confirm('Are you sure?')) {
                                return false;
                            }

                            waitingScreen.show();
                            var realtor = createRealtor();
                            realtor.id  = val.id;
                            realtor.delete().then(function() {
                                renderMessage('Realtor was deleted.', 'success', scope.container, scope);
                                scope.realtors.splice(key, 1);
                            }).catch(function(data) {
                                renderMessage(data.message, 'danger', scope.container, scope);
                            }).finally(function() {
                                waitingScreen.hide();
                            });

                            realtor = null;
                        };

                        waitingScreen.show();

                        $http.get(PATH, {params: $location.search()}).success(function(data) {
                            for (var i in data.realtors) {
                                scope.realtors.push(createRealtor().fill(data.realtors[i]));
                            }

                            scope.pagination      = data.pagination;
                            scope.searchingString = $location.search()[data.keySearch];
                            scope.searchKey       = data.keySearch;

                            function params(settings) {
                                this.key   = settings.key;
                                this.title = settings.title;
                            }

                            params.prototype.directionKey     = data.keyDirection;
                            params.prototype.sortKey          = data.keySort;
                            params.prototype.defaultDirection = data.defDirection;
                            params.prototype.defaultSortKey   = data.defField;

                            scope.headParams = [
                                new tableHeadCol(new params({key: 'id', title: 'id', isSortable: true})),
                                new tableHeadCol(new params({key: 'photo', title: 'Photo', isSortable: false})),
                                new tableHeadCol(new params({key: 'first_name', title: 'First name', isSortable: true})),
                                new tableHeadCol(new params({key: 'last_name', title: 'Last name', isSortable: true})),
                                new tableHeadCol(new params({key: 'email', title: 'Email', isSortable: true})),
                                new tableHeadCol(new params({key: 'phone', title: 'Phone', isSortable: false})),
                                new tableHeadCol(new params({key: 'bre_number', title: 'BRE number', isSortable: false})),
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
        ]
    );

    realtorModule.directive(
        'loAdminRealtorForm',
        ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll', 'pictureObject', '$http',
            function(waitingScreen, renderMessage, sessionMessages, $anchorScroll, pictureObject, $http)
            {
                return {
                    restrict   : 'EA',
                    templateUrl: '/partials/admin.realtor.form',
                    scope      : { realtor: '=loRealtor' },
                    link       : function(scope, element, attrs, controllers) {
                        scope.container       = angular.element('#realtorMessage');
                        scope.realtorPicture  = {};
                        scope.realtyCompanies = [];
                        scope.hideErrors      = true;

                        scope.$watch('realtor.id', function(newVal, oldVal) {
                            scope.title = newVal? 'Edit Realtor': 'Add Realtor';
                        });

                        scope.$watch('realtor', function(newVal, oldVal) {
                            if (newVal == undefined || !('id' in newVal)){
                                return;
                            }
                            scope.realtorPicture = new pictureObject(
                                angular.element('#realtorImage'),
                                {
                                    container: $('.realtor.realtor-photo > img'),
                                    options: {aspectRatio: 3 / 4, minContainerWidth: 100}
                                },
                                scope.realtor
                            );
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
                            if (scope.realtorPicture && scope.realtor.photo !== null) {
                                scope.realtorPicture.prepareImage(800, 400, 600, 300);
                            }
                            if (!formSalesDirector.$valid) {
                                this.hideErrors = false;
                                this.gotoErrorMessage();
                                return false;
                            }
                            this.save();
                        };

                        scope.save = function() {
                            waitingScreen.show();

                            scope.realtor.save().then(function(data) {
                                sessionMessages.addSuccess('Successfully saved.');
                                history.back();
                            }).catch(function(data) {
                                var errors = '';
                                if ('message' in data) {
                                    errors += data.message+' ';
                                }

                                if ('form_errors' in data) {
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
                            scope.realtor.delete().then(function() {
                                sessionMessages.addSuccess('Sales director was deleted.');
                                scope.realtor.clear();
                                history.back();
                            }).catch(function(data) {
                                renderMessage(data.message, 'danger', scope.container, scope);
                            }).finally(function() {
                                waitingScreen.hide();
                            });
                        };

                        $http.get('/admin/realty/all').success(function(data) {
                            if (scope.realtor.hasOwnProperty('realty_company_id')
                                && scope.realtor.realty_company_id !== null
                            ) {
                                scope.realtor.realty_company_id += '';
                            }
                            scope.realtyCompanies = data;
                        });
                    }
                }
            }
        ]);
})(settings);
