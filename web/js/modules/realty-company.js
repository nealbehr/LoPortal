(function(settings){
    'use strict';
    settings = settings || {};

    var realtyCompanyModule = angular.module('realtyCompanyModule', ['adminModule']);

    realtyCompanyModule.service("createRealtyCompany", ["$q","$http", "createRealtyCompanyBase", function($q, $http, createRealtyCompanyBase){
        return function() {
            var realtyBase = new createRealtyCompanyBase();

            realtyBase.delete = function() {
                if(!this.id){
                    alert('Realty Company id has not set.');
                }
                var deferred = $q.defer();
                $http.delete('/admin/realty/' + this.id, {})
                    .success(function(data) {
                        if(data.status == 'error') {
                            deferred.reject(data);
                        } else {
                            deferred.resolve(data);
                        }
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            realtyBase.save = function(){
                return this.id? this.update(): this.add();
            };

            realtyBase.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/realty/' + this.id, {company: this.getFields4Save()})
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            realtyBase.add = function(){
                var deferred = $q.defer();
                $http.post('/admin/realty', {company: this.getFields4Save()})
                    .success(function(data){
                        realtyBase.id = data.id;
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            return realtyBase;
        }
    }]);

    realtyCompanyModule.service("createRealtyCompanyBase", ["$http","$q", function($http, $q){
        return function () {

            this.id = null;
            this.name = null;
            this.logo = null;

            var self = this;

            this.getPicture = function(){
                return this.logo;
            };
            this.setPicture = function(param){
                this.logo = param;
                return this;
            };

            this.fill = function(data){
                for(var key in data) {
                    if (data.hasOwnProperty(key)) {
                        this[key] = data[key];
                    }
                }
                return this;
            };

            this.getFields4Save = function(){
                var result = {};
                for(var key in this) {
                    if (this.hasOwnProperty(key)) {
                        if(typeof this[key] == 'function'){
                            continue;
                        }
                        result[key] = this[key];
                    }
                }
                return result;
            };

            this.get = function(id){
                var deferred = $q.defer();
                $http.get('/admin/realty/' + id)
                    .success(function(data){
                        self.fill(data);
                        deferred.resolve(self)
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            };

            this.clear = function() {
                for(var key in this){
                    if (this.hasOwnProperty(key)) {
                        if(typeof this[key] == 'function'){
                            continue;
                        }
                        this[key] = undefined;
                    }
                }
            };

            this.save = function(){
                throw new Error("User add must be override");
            };

            this.add = function(){
                throw new Error("Request add must be override");
            };

            this.update = function(){
                throw new Error("Request update must be override");
            };

        }
    }]);

    realtyCompanyModule.factory('realtyLogosFactory', ['$http', function($http) {

        var urlBase = '/settings/realty-companies';

        var factory = {};
        factory.getRealtyCompanies = function() {
            //return realtyCompanies;
            return $http.get(urlBase);
        };
        return factory;
    }]);

    realtyCompanyModule.controller('SelectRealtyLogoController', ['$scope', 'realtyLogosFactory', function($scope, realtyLogosFactory) {

        $scope.realtyCompanies = [];

        getRealtyCompanies();

        function getRealtyCompanies() {
            realtyLogosFactory.getRealtyCompanies()
                .success(function (companies) {
                    $scope.realtyCompanies = companies;
                })
                .error(function (error) {
                    $scope.status = 'Unable to load realty companies data: ' + error.message;
                });
        }

        $scope.selectRealtyLogo = function(realtyCompany) {
            if($scope.realtyLogo) {
                $scope.realtyLogo.cropperDestroy();
            }
            $scope.request.realtor.realty_name = realtyCompany.name;
            $scope.request.realtor.realty_logo = realtyCompany.logo;
            $('#chooseRealtyCompanyLogo').modal('hide');
        };
    }]);

    realtyCompanyModule.controller('AdminCompaniesController', ['$scope', function($scope) {
        $scope.settings = settings;
    }]);

    realtyCompanyModule.controller('AdminRealtyNewController', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createRealtyCompany', function($scope, $http, redirect, $compile, waitingScreen, createRealtyCompany) {
        $scope.company = createRealtyCompany();
    }]);

    realtyCompanyModule.controller('AdminRealtyEditController', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createRealtyCompany', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createRealtyCompany, $routeParams){
        createRealtyCompany().get($routeParams.id)
            .then(function(company){
                $scope.company = company;
            });
    }]);
})(settings);