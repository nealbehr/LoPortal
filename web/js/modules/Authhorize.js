(function(settings){
    "use strict";
    settings = settings || {};

    var authorize = angular.module('authModule', []);

    authorize.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/login', {
                templateUrl: '/partials/login',
                controller:  'authorizeCtrl'
            });
    }]);

    authorize.controller('authorizeCtrl', ['$scope', '$http', 'redirect', '$cookieStore', 'TOKEN_KEY', function($scope, $http, redirect, $cookieStore){
        $scope.user = {email: "", password: ""};
        $scope.errorMessage = null;

        $scope.isValidEmail = function(formLogin){
            return (formLogin.$submitted || formLogin.email.$touched) && (formLogin.email.$error.email || formLogin.email.$error.required);
        }

        $scope.isValidPassword = function(formLogin){
            return (formLogin.$submitted || formLogin.password.$touched) && (formLogin.password.$error.required);
        }

        $scope.complete = function($event){
            angular.element($event.target).autocomplete({
                source: function( request, response ) {
                    $http.get('/authorize/autocomplete/' + request.term)
                        .success(function(data){
                            response(data);
                        })
                    ;
                },
                minLength: 3
            });
        }

        $scope.submit = function(){
            this.errorMessage = null;
            // 1) show gray screen
            $http.post('/authorize/signin', $.param(this.user))
                .success(function(data){
                    $cookieStore.put('access_token', data);
                    redirect('/');
                })
                .error(function(data){
                    $scope.errorMessage = data.message;
                })
                .finally(function(){
                    // 1) hide gray screen
                });
            ;
        }
    }]);
})(settings);