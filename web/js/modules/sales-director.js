(function(settings){
    'use strict';
    settings = settings || {};

    var PATH = '/admin/salesdirector';

    var salesDirectorModule = angular.module('salesDirectorModule', ['adminModule']);

    salesDirectorModule.factory('getSalesDirectors', ['$q', '$http', function($q, $http) {
        var salesDirectors = [];

        return function(needReload) {
            var deferred = $q.defer();
            if (salesDirectors.length !== 0 && !needReload) {
                return $q.when(salesDirectors);
            }

            $http.get(PATH).success(function(data) {
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
            var salesDirector = new createSalesDirectorBase();

            salesDirector.getList = function(needReload) {
                return getSalesDirectors(needReload)
            };

            salesDirector.delete = function() {
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

            salesDirector.save = function() {
                return this.id ? this.update() : this.add();
            };

            salesDirector.update = function() {
                var deferred = $q.defer();
                $http.put(PATH+'/'+this.id, {salesDirector: this.getFields4Save()}).success(function(data){
                    deferred.resolve(data);
                }).error(function(data){
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            salesDirector.add = function() {
                var deferred = $q.defer();
                $http.post(PATH, {salesDirector: this.getFields4Save()}).success(function(data) {
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
})(settings);
