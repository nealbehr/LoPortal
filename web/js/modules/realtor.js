(function(settings) {
    'use strict';
    settings = settings || {};

    var PATH   = '/admin/realtor';

    var module = angular.module('queueRealtorModule', ['adminModule']);

    module.factory('getRealtor', ['$q', '$http', function($q, $http) {
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

    module.service(
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

    module.service('createRealtorBase', ['$q', '$http', function($q, $http) {
        return function() {
            var self = this;

            this.id                = null;
            this.first_name        = null;
            this.last_name         = null;
            this.bre_number        = null;
            this.email             = null;
            this.phone             = null;
            this.photo             = null;
            this.realty_logo       = null;
            this.realty_name       = null;

            this.getPhoto = function() {
                return this.photo;
            };

            this.setPhoto = function(param) {
                this.photo = param;
                return this;
            };

            this.getRealtyLogo = function() {
                return this.realty_logo;
            };

            this.setRealtyLogo = function(param) {
                this.realty_logo = param;
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

    module.controller('queueRealtorCtrl', ['$scope', 'createRealtor', function($scope, createRealtor) {
        $scope.realtor = createRealtor();
        $scope.PATH    = PATH;
    }]);

    module.controller(
        'queueRealtorEditCtrl',
        ['$scope', 'createRealtor', '$routeParams', 'waitingScreen',
            function($scope, createRealtor, $routeParams, waitingScreen)
        {
            waitingScreen.show();

            createRealtor().get($routeParams.id).then(function(data) {
                $scope.realtor = data;
            }).finally(function() {
                waitingScreen.hide();
            });
            $scope.PATH    = PATH;
        }
    ]);

    module.controller('selectRealtyCompanyCtrl', ['$scope', 'realtyLogosFactory', function($scope, realtyLogosFactory) {
        $scope.realtyCompanies = [];

        getRealtyCompanies();

        function getRealtyCompanies() {
            realtyLogosFactory.getRealtyCompanies().success(function (companies) {
                $scope.realtyCompanies = companies;
            }).error(function (error) {
                $scope.status = 'Unable to load realty companies data: '+error.message;
            });
        }

        $scope.selectRealtyLogo = function(e, realtyCompany) {
            e.preventDefault();

            if($scope.realtyLogo) {
                $scope.realtyLogo.cropperDestroy();
            }
            $scope.realtor.realty_name = realtyCompany.name;
            $scope.realtor.realty_logo = realtyCompany.logo;
            $('#chooseRealtyCompanyLogo').modal('hide');
        };
    }]);

    module.directive(
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

    module.directive(
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
                        scope.realtyLogo      = {};
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
                                    options  : {aspectRatio: 3 / 4, minContainerWidth: 100}
                                },
                                scope.realtor,
                                'setPhoto'
                            );

                            scope.realtyLogo = new pictureObject(
                                angular.element('#realtyLogo'),
                                {
                                    container: $('#realtyLogoImage'),
                                    options  : {
                                        minCropBoxWidth : 100,
                                        minCropBoxHeight: 100,
                                        maxCropBoxWidth : 350,
                                        maxCropBoxHeight: 100
                                    }
                                },
                                scope.realtor,
                                'setRealtyLogo'
                            );
                        });

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
