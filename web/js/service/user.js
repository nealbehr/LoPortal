(function(settings){
    'use strict';
    settings = settings || {};

    var userService = angular.module('userModule', []);

    userService.factory("userService", ["createProfileUser", function(createProfileUser){
        console.log('called new User');
        return createProfileUser();
    }]);

    userService.service("createProfileUser", ["$q", "$http", "createUserBase", function($q, $http, createUserBase){
        return function(){
            var userBase = new createUserBase();

            userBase.save = function(){
                var deferred = $q.defer();
                console.log(this.getFields4Save())
                $http.put('/user/' + this.id, {user: this.getFields4Save()})
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

            return userBase;
        }
    }]);

    userService.service("createUser", ["$q", "$http", "createUserBase", function($q, $http, createUserBase){
        return function(){
            var userBase = new createUserBase();

            userBase.delete = function(){
                if(!this.id){
                    alert('User id has not set.');
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

            userBase.save = function(){
                return this.id? this.update(): this.add();
            }

            userBase.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/user/' + this.id, {user: this.getFields4Save()})
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

            userBase.add = function(){
                var deferred = $q.defer();
                $http.post('/admin/user', {user: this.getFields4Save()})
                    .success(function(data){
                        userBase.id = data.id;
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

            userBase.resetPassword = function(){
                var deferred = $q.defer();
                $http.patch('/admin/user/' + this.id, {})
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


            return userBase;
        }
    }]);

    userService.service("createUserBase", ["$q", "$http", function($q, $http){
        return function(){
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
            this.lender;
            this.roles = {};
            this.switched = false;

            var self = this;

            this.getFields4Save = function(){
                var result = {};
                for(var i in this){
                    if(typeof this[i] == 'function'){
                        continue;
                    }

                    result[i] = this[i];
                }

                return result;
            }

            this.isSwitched = function(){
                return this.switched;
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
                        self.fill(data);

                        deferred.resolve(self)
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

            this.fill = function(data){
                for(var i in data){
                    this[i] = data[i];
                }

                return this;
            }

            this.clear = function(){
                for(var i in this){
                    if(typeof this[i] == 'function'){
                        continue;
                    }

                    this[i] = undefined;
                }

                this.roles = [];
                this.isLogged = false;
            }

            this.save = function(){
                throw new Error("User add must be override");
            }
        }
    }]);
})(settings);