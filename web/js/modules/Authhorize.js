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

    authorize.controller('authorizeCtrl', ['$scope', '$http', 'redirect', '$cookieStore', 'TOKEN_KEY', function($scope, $http, redirect, $cookieStore, TOKEN_KEY){
        if($cookieStore.get(TOKEN_KEY)){
            redirect('/');
        }

        $scope.user = {email: "", password: ""};
        $scope.errorMessage          = null;
        $scope.emailForResetPassword = null;

        $scope.isValidEmail = function(form){
            if(!form.email){
                return;
            }

            return (form.$submitted || form.email.$touched) && (form.email.$error.email || form.email.$error.required);
        }

        $scope.isValidPassword = function(form){
            if(!form.password){
                return;
            }

            return (form.$submitted || form.password.$touched) && (form.password.$error.required);
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

        $scope.signin = function(){
            this.errorMessage = null;
            // 1) show gray screen
            $http.post('/authorize/signin', $.param(this.user))
                .success(function(data){
                    $cookieStore.put(TOKEN_KEY, data);
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

        $scope.resetPassword = function(form){
            console.log(form);
        }
    }]);
})(settings);