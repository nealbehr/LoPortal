(function(settings){
    'use strict';
    settings = settings || {};

    var userService = angular.module('userModule', []);

    userService.factory("userService", ["createUser", function(createUser){
        console.log('called new User');
        return createUser();
    }]);

    userService.service("createUser", ["$q", "$http", function($q, $http){
        return function(){
            return new function(){
                this.isLogged = false;
                this.id;
                this.first_name;
                this.last_name;
                this.title;
                this.sales_director;
                this.phone;
                this.mobile;
                this.email;
                this.nmls;
                this.roles = {};

                var self = this;

                var getFields4Save = function(){
                    var result = {};
                    for(var i in self){
                        if(typeof self[i] == 'function'){
                            continue;
                        }

                        result[i] = self[i];
                    }

                    return result;
                }

                this.isAdmin = function(){
                    for(var i in this.roles){
                        if(this.roles[i] == 'ROLE_ADMIN'){
                            return true;
                        }
                    }

                    return false;
                }

                this.get = function(id){
                    id = id || "me";
                    if(this.isLogged){
                        return $q.when(this);
                    }

                    var deferred = $q.defer();
                    $http.get('/user/' + id)
                        .success(function(data){
                            self.isLogged = true;
                            for(var i in data){
                                self[i] = data[i];
                            }

                            deferred.resolve(self)
                        })
                        .error(function(data){
                            deferred.reject(data);
                        })
                    ;

                    return deferred.promise;
                }

                this.clear = function(){
                    for(var i in self){
                        if(typeof self[i] == 'function'){
                            continue;
                        }

                        this[i] = undefined;
                    }

                    this.roles = [];
                    this.isLogged = false;
                }

                this.delete = function(){
                    if(!this.id){
                        alert('Have not set user id.');
                    }

                    var deferred = $q.defer();
                    $http.delete('/admin/user/' + this.id, {})
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

                this.save = function(){
                    return this.id? this.update(): this.add();
                }

                this.update = function(){
                    var deferred = $q.defer();
                    $http.put('/admin/user/' + this.id, {user: getFields4Save()})
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

                this.add = function(){
                    var deferred = $q.defer();
                    $http.post('/admin/user', {user: getFields4Save()})
                        .success(function(data){
                            self.id = data.id;
                            deferred.resolve(data);
                        })
                        .error(function(data){
                            console.log(data);
                            deferred.reject(data);
                        })
                    ;

                    return deferred.promise;
                }


            }
        }
    }]);


})(settings);