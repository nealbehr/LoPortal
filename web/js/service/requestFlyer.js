(function(settings){
    'use strict';
    settings = settings || {};

    var flyerService = angular.module('requestFlyerModule', []);

    flyerService.service("createFromPropertyApproval", ["$http", "createRequestFlyerBase", "$q", function($http, createRequestFlyerBase, $q){
        function extend(Child, Parent) {
            var F = function() { }
            F.prototype = Parent.prototype
            Child.prototype = new F()
            Child.prototype.constructor = Child
            Child.superclass = Parent.prototype
        }

        function fromPropertyApproval(id){
            fromPropertyApproval.superclass.constructor.call(this);
            if(id){
                this.id = id;
            }
            var flyer = this;

            this.save = function(){
                this.property.state = settings.queue.state.listingFlyerPending;
                return $http.put('/request/from/approval/' + this.id, this.getFields4Save());
            }
        }

        extend(fromPropertyApproval, createRequestFlyerBase);

        return fromPropertyApproval;
    }]);

    flyerService.service("createRequestFlyer", ["$http", "createRequestFlyerBase", function($http, createRequestFlyerBase){
        return function(){
            var flyer = new createRequestFlyerBase();

            flyer.save = function(){
                return $http.post('/request/', this.getFields4Save());
            }

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

            flyer.save = function(){
                return this.id? this.update(): this.add();
            }

            flyer.update = function(){
                var deferred = $q.defer();
                if(this.property.state == settings.queue.state.draft){
                    this.property.state = settings.queue.state.requested;
                }
                $http.put('/request/' + this.id, this.getFields4Save())
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

    flyerService.service("createRequestFlyerBase", ["$http", function($http){
        return function flyerBase(){
            this.id = null;

            this.property = {
                address: null,
                mls_number: null,
                state: settings.queue.state.requested,
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
                this.fillObject(data);

                return this;
            }

            this.fillObject = function(data, object){
                object = object || this;
                var result = {};
                for(var i in data){
                    if(!(i in object)){
                        continue;
                    }

                    if(typeof data[i] == "object" && data[i] !== null){
                        this.fillObject(data[i], object[i]);
                    }else{
                        object[i] = data[i];
                    }
                }
            }

            this.getFields4Save = function(object){
                object = object || this;
                var result = {};
                for(var i in object){
                    if (typeof object[i] === "function"){
                        continue;
                    }

                    result[i] = (typeof object[i] === "object" && object[i] !== null)
                                        ? this.getFields4Save(object[i])
                                        : object[i];
                }

                return result;
            }

            this.save = function(){
                throw new Error("Request save must be override");
            }

            this.draftSave = function(){
                return this.id? this.draftUpdate(): this.draftAdd();
            }

            this.draftUpdate = function(){
                return $http.put('/request/draft/' + this.id, this.getFields4Save());
            }

            this.draftAdd = function(){
                this.property.state = settings.queue.state.draft;
                return $http.post('/request/draft', this.getFields4Save());
            }
        }
    }]);
})(settings);