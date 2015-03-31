(function(settings){
    'use strict';
    settings = settings || {};

    var UserService = angular.module('userModule', []);

    UserService.factory("userService", ['$q', '$http', function($q, $http){
        return new function(){
            console.log('called new User');
            this.isLogged = false;
            this.first_name = '';
            this.last_name = '';
            this.title = '';
            this.sales_director = '';
            this.phone = '';
            this.email = '';
            this.nmls = '';

            var self = this;

            this.get = function(){
                if(this.isLogged){
                    return $q.when(this);
                }

                var deferred = $q.defer();
                $http.get('/user/me')
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
        }
    }]);
})(settings);