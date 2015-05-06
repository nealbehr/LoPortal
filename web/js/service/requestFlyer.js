(function(settings){
    'use strict';
    settings = settings || {};

    var flyerService = angular.module('requestFlyerModule', []);

    flyerService.service("createRequestFlyer", ["$q", "$http", "createRequestFlyerBase", function($q, $http, createRequestFlyerBase){
        return function(){
            var flyer = new createRequestFlyerBase();

            flyer.save = function(){
                var deferred = $q.defer();
                $http.post('/request/', this.getFields4Save())
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

            return flyer;
        }
    }]);

    flyerService.service("createAdminRequestFlyer", ["$q", "$http", "createRequestFlyerBase", function($q, $http, createRequestFlyerBase){
        console.log("createAdminRequestFlyer");
        return function(){
            var flyer = new createRequestFlyerBase();

            flyer.get = function(id){
                if(this.id !== null){
                    return $q.when(this);
                }

                var deferred = $q.defer();
                $http.get('/admin/flyer/' + id)
                    .success(function(data){
                        flyer.id = id;
                        flyer.fill(data);

                        deferred.resolve(flyer)
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

            flyer.save = function(){
                return this.id? this.update(): this.add();
            }

            flyer.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/flyer/' + this.id, this.getFields4Save())
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

            flyer.add = function(){
                throw new Error("ID not found");
            }

            return flyer;
        }
    }]);

    flyerService.service("createRequestFlyerBase", [function(){
        return function(){
            this.id = null;

            this.property = {
                first_rex_id: null,
                address: null,
                mls_number: null,
                listing_price: null,
                photo: null,
                getPicture: function(){
                    return this.photo;
                },
                setPicture: function(param){
                    this.photo = param;

                    return this;
                }
            };

            this.realtor = {
                first_name: null,
                last_name: null,
                bre_number: null,
                estate_agency: null,
                phone: null,
                email: null,
                photo: null,
                getPicture: function(){
                    return this.photo;
                },
                setPicture: function(param){
                    this.photo = param;

                    return this;
                }
            }

            this.fill = function(data){
                for(var i in data){
                    this[i] = data[i];
                }

                return this;
            }

            this.getFields4Save = function(object){
                object = object || this;
                var result = {};
                for(var i in object){
                    if(object[i] == undefined){
                        result[i] = null;
                    }else if(typeof object[i] === "object" && object[i] !== null){
                        result[i] = this.getFields(object[i]);
                    }
                }

                return result;
            }

            this.save = function(){
                throw new Error("Request save must be override");
            }
        }
    }]);
})(settings);