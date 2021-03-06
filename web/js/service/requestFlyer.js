(function(settings){
    'use strict';
    settings = settings || {};

    var flyerService = angular.module('requestFlyerModule', []);

    function extend(Child, Parent) {
        var F = function() { };
        F.prototype = Parent.prototype;
        Child.prototype = new F();
        Child.prototype.constructor = Child;
        Child.superclass = Parent.prototype;
    }

    flyerService.service("createFromPropertyApproval", ["$http", "createRequestFlyerBase", function($http, createRequestFlyerBase){
        function fromPropertyApproval(id){
            fromPropertyApproval.superclass.constructor.call(this);
            if(id){
                this.id = id;
            }

            this.update = function(){
                return $http.put('/request/from/approval/' + this.id, this.getFields4Save());
            }
        }

        extend(fromPropertyApproval, createRequestFlyerBase);

        return fromPropertyApproval;
    }]);

    flyerService.service("createDraftFromPropertyApproval", ["$http", "createRequestFlyerBase", function($http, createRequestFlyerBase){
        function fromPropertyApproval(id){
            fromPropertyApproval.superclass.constructor.call(this);
            if(id){
                this.id = id;
            }

            this.update = function(){
                return $http.put('/request/from/approval/draft/' + this.id, this.getFields4Save());
            }
        }

        extend(fromPropertyApproval, createRequestFlyerBase);

        return fromPropertyApproval;
    }]);

    flyerService.service("createDraftRequestFlyer", ["$http", "createRequestFlyerBase", function($http, createRequestFlyerBase){
        return function(id) {
            var flyer = new createRequestFlyerBase();

            if (id) {
                flyer.id = id;
            }

            flyer.update = function() {
                return $http.put('/request/flyer/' + this.id, this.getFields4Save());
            };

            flyer.add = function(){
                return $http.post('/request/draft', this.getFields4Save());
            };

            flyer.remove = function(){
                return $http.delete('/request/draft/' + this.id);
            };

            return flyer;
        }
    }]);

    flyerService.service("createRequestFlyer", ["$http", "createRequestFlyerBase", function($http, createRequestFlyerBase){
        console.log("createRequestFlyer");
        return function(id){
            var flyer = new createRequestFlyerBase();

            if(id){
                flyer.id = id;
            }

            flyer.add = function(){
                return $http.post('/request/', this.getFields4Save());
            };

            flyer.update = function(){
                return $http.put('/request/' + this.id, this.getFields4Save());
            };

            return flyer;
        }
    }]);

    flyerService.service("createAdminRequestFlyer", ["$q", "$http", "createRequestFlyerBase", function($q, $http, createRequestFlyerBase){
        console.log("createAdminRequestFlyer");
        return function(id){
            var flyer = new createRequestFlyerBase();

            if(id){
                flyer.id = id;
            }

            flyer.update = function(){
                return $http.put('/admin/flyer/' + this.id, this.getFields4Save());
            };
            return flyer;
        }
    }]);

    flyerService.service("createRequestFlyerBase", ["$http","$q", function($http, $q){
        return function flyerBase(){
            var self = this;

            this.id         = null;
            this.realtor_id = null;

            this.property = {
                id: null,
                archive: '0',
                address: null,
                apartment: null,
                mls_number: null,
                state: null,
                omit_realtor_info: '1',
                listing_price: null,
                funded_percentage: 10.00,
                maximum_loan: 80.00,
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
                phone: null,
                email: null,
                photo: null,
                realty_name: null,
                realty_logo: null,

                getPicture: function() {
                    return this.photo;
                },
                setPicture: function(param) {
                    this.photo = param;
                    return this;
                },
                getRealtyLogo: function() {
                    return this.realty_logo;
                },
                setRealtyLogo: function(param) {
                    this.realty_logo = param;
                    return this;
                }
            };

            this.address = {
                address: '',
                city:    null,
                state:   null,
                zip:     null,
                clear: function() {
                    this.address = '';
                    this.city    = null;
                    this.state   = null;
                    this.zip     = null;
                },
                set: function(data){
                    for(var i in data){
                        if(i in data){
                            this[i] = data[i];
                        }
                    }
                }
            };

            this.fill = function(data){
                this.fillObject(data);

                return this;
            };

            this.fillObject = function(data, object){
                object = object || this;
                var result = {};
                for(var i in data){
                    if(!(i in object) || data[i] == null){
                        continue;
                    }

                    if(typeof data[i] == "object"){
                        this.fillObject(data[i], object[i]);
                    } else{
                        object[i] = data[i];
                    }
                }
            };

            this.getFields4Save = function(object){
                object = object || this;
                var result = {};
                for(var i in object){
                    if (typeof object[i] === "function"){
                        continue;
                    }

                    // Replase commas and symbol $
                    if (i == 'listing_price' && null != object[i]) {
                        result[i] = (''+object[i]).replace(/(\$|,)/g, '');
                        continue;
                    }

                    result[i] = (typeof object[i] === "object" && object[i] !== null)
                                        ? this.getFields4Save(object[i])
                                        : object[i];
                }

                return result;
            };

            this.get = function(id) {
                var deferred = $q.defer();
                $http.get('/request/'+id).success(function(data) {
                    self.fill(data);
                    deferred.resolve(self)
                }).error(function(data) {
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            this.save = function() {
                var deferred = $q.defer();
                (function(){ return self.id? self.update(): self.add(); })().success(function(data){
                        self.fillObject(data);
                        self.afterSave();
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            };

            this.add = function(){
                throw new Error("Request add must be override");
            };

            this.update = function(){
                throw new Error("Request update must be override");
            };

            var afterSaveCallback;
            this.afterSave = function(callback){
                if(callback){
                    afterSaveCallback = callback;

                    return;
                }

                if(typeof afterSaveCallback == 'function'){
                    afterSaveCallback();
                }
            }
        }
    }]);
})(settings);