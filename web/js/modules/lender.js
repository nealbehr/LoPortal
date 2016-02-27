(function(settings){
    'use strict';
    settings = settings || {};

    var lenderModule = angular.module('lenderModule', ['adminModule']);

    lenderModule.service("createLender", ["$q","$http", "createLenderBase", function($q, $http, createLenderBase){
        return function() {
            var lenderBase = new createLenderBase();

            lenderBase.delete = function() {
                if(!this.id){
                    alert('Lender id has not set.');
                }
                var deferred = $q.defer();
                $http.delete('/admin/lender/' + this.id, {})
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

            lenderBase.save = function(){
                return this.id? this.update(): this.add();
            };

            lenderBase.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/lender/' + this.id, {lender: this.getFields4Save()})
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            lenderBase.add = function(){
                var deferred = $q.defer();
                $http.post('/admin/lender', {lender: this.getFields4Save()})
                    .success(function(data){
                        lenderBase.id = data.id;
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            return lenderBase;
        }
    }]);

    lenderModule.service("createLenderBase", ["$http","$q", function($http, $q){
        return function () {

            this.id = null;
            this.name = null;
            this.picture = null;
            this.disclosures = [];

            var self = this;

            this.getPicture = function(){
                return this.picture;
            };
            this.setPicture = function(param){
                this.picture = param;
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

            this.getFields4Save = function() {
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
                $http.get('/admin/lender/' + id)
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

    lenderModule.controller('AdminLendersController', ['$scope', function($scope){
        $scope.settings = settings;
    }]);

    lenderModule.controller('AdminNewLenderController', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createLender', function($scope, $http, redirect, $compile, waitingScreen, createLender) {
        $scope.lender = createLender();
    }]);

    lenderModule.controller('AdminEditLenderController', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createLender', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createLender, $routeParams){
        createLender().get($routeParams.id)
            .then(function(lender){
                $scope.lender = lender;
            });
    }]);
})(settings);