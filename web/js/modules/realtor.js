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
})(settings);
